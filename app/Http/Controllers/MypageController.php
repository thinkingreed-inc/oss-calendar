<?php

namespace App\Http\Controllers;

use App\Http\Requests\Mypage\PasswordUpdateRequest;
use App\Http\Requests\Mypage\MySettingStoreRequest;
use App\Models\DefaultReminder;
use App\Models\User;
use App\Models\ChangeEmail;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use phpseclib\Crypt\Hash;

class MypageController extends Controller
{
    /**
     * パスワード更新
     *
     * @param PasswordUpdateRequest $request
     * @param $idresend
     */
    public function password(PasswordUpdateRequest $request)
    {
        //
        $myuser = \Auth::user();
        $password = $request->get("password");
        $myuser->password = \Hash::make($password);
        $myuser->save();
    }

    public function setting(MySettingStoreRequest $request)
    {
        //
        $myuser = \Auth::user();
        $myuser->home_page_id = $request->home_page_id;
        $myuser->save();

        // 通知設定更新
        $this->saveDefaultReminder($request, $myuser->id, $myuser);
    }

    /**
     * メール再送
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resend(Request $request)
    {
        //
        $user = \Auth::user();
        $changeEmail= ChangeEmail::where('user_id', $user->id)->first();

        if( $changeEmail == null ) {
            return response()->json(['message' => '確認メールはありません。', 'status' => true], 401);
        }

        $user->sendEmailChangeNotification(
            $this->tokenCreate( $changeEmail->email)
        );

        return response()->json(['message' => 'メールアドレスを再送しました。', 'status' => true], 201);
    }

    /**
     * 確認メール取消
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel(Request $request)
    {
        //
        $user = \Auth::user();
        ChangeEmail::where('user_id', $user->id)->delete();

        return response()->json(['message' => '確認メールを取消しました。', 'status' => true], 201);
    }

    /**
     * 確認メールのメールアドレス取得
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConfirmEmail(Request $request)
    {
        //
        $user = \Auth::user();
        $changeEmail = ChangeEmail::where('user_id', $user->id)->first();

        if( $changeEmail == null) {
            return ['email' => ''];
        }
        else {
            return ['email' => $changeEmail->email];
        }
    }

    /**
     * メールアドレス変更
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function sendChangeLinkEmail(Request $request)
    {
        // バリデート
        $this->validateEmail($request);

        $user = \Auth::user();
        $change_email = $request->get('email');
        // パスワードリセットトークンを生成し
        // パスワードリセットメールを送信する
        $user->sendEmailChangeNotification(
            $this->tokenCreate( $change_email)
        );

        return response()->json(['message' => 'メールアドレスを送信しました。', 'status' => true], 201);
    }

    protected function validateEmail(Request $request)
    {
        // メールアドレスがユーザーテーブル、メールアドレス変更テーブルに入っていれば、除外する。
        $this->validate($request, ['email' => 'required|email|unique:users,email|unique:change_emails,email']);
    }

    protected function tokenCreate($change_email)
    {
        $user = \Auth::user();

        // DBにメール変更トークンが存在する場合は削除する
        ChangeEmail::where('user_id', $user->id)->delete();

        // トークン生成
        $token = hash_hmac('sha256', \Str::random(40), config('app.key'));

        // DBにトークンを保存する
        $insertData = ['email' => $change_email, 'token' => \Hash::make($token), 'created_at' => new Carbon, 'user_id' => $user->id];
        ChangeEmail::insert($insertData);

        // トークンを呼び出し元に返す。
        // このトークンがメールのリンクに付与される
        return $token;
    }

    /**
     * 最終メールアドレス更新処理（メール変更後の確認メールのリンクをクリックしたとき）
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  $token
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function checkEmail(Request $request)
    {
        $request->validate($this->rules());
        $token = $request->get('token');

        $user = \Auth::user();
        $changeEmail= ChangeEmail::where('user_id', $user->id)->first();

        if( $changeEmail == null) {
            return response()->json(['message' => "すでに確認しました。", 'status' => false], 201);
        }
        elseif ( $this->tokenExpired($changeEmail->created_at) ) {
            return response()->json(['message' => 'トークンの期限切れです。再度、メールドレス変更画面から確認メールの再発行を行ってください。', 'status' => false], 401);
        }
        elseif ( !$this->checkToken($token, $changeEmail->token) ) {
            return response()->json(['message' => 'トークン認証に失敗しました。再度、メールドレス変更画面から確認メールの再発行を行ってください。', 'status' => false], 401);
        }
        else {
            $email = $changeEmail->email;
            ChangeEmail::where('user_id', $user->id)->delete();
            $user->email = $email;
            $user->save();
            return response()->json(['message' => "新しいメールアドレス {$email} に変更しました。", 'status' => false], 201);
        }
    }

    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
        ];
    }

    /**
     * トークンの有効期限
     *
     * @param  string  $createdAt
     * @return bool
     */
    protected function tokenExpired($createdAt)
    {
        // 10分で期限切れとする。
        return Carbon::parse($createdAt)->addSeconds(600)->isPast();
    }

    /**
     * トークンの確認
     *
     * @param $value
     * @param $hashedValue
     * @return bool
     */
    protected function checkToken($value, $hashedValue) {
        return \Hash::check($value, $hashedValue);
    }


    /**
     * 通知設定更新
     * @param Request $request
     * @param null $id
     * @param $user
     * @return mixed
     */
    public function saveDefaultReminder(Request $request, $id = null, $user){
        $requestData = $request->all();

        $default_reminder = DefaultReminder::where('calendarlist_id', $user->calendarlist_id)->first();
        if($default_reminder) {
            // 更新
            $default_reminder->calendarlist_id = $user->calendarlist_id;
            $default_reminder->default_reminders_method_id = $requestData["default_reminders_method_id"];
            $default_reminder->overrides_minutes = $requestData["overrides_minutes"];
            $default_reminder->save();
        }else{
            // 登録
            $default_reminder = new DefaultReminder();
            $default_reminder->calendarlist_id = $user->calendarlist_id;
            $default_reminder->default_reminders_method_id = 1;
            $default_reminder->overrides_minutes = 10;
            $default_reminder->save();
        }

        return $user;
    }

    /**
     * @param $id
     */
    public function destroyDefaultReminder($id){
        $user = User::where('id', $id)->first();
        // 削除
        $submodels = DefaultReminder::where('calendarlist_id', $user->calendarlist_id)->get();
        foreach($submodels as $submodel){
            DefaultReminder::destroy($submodel->id);
        }
    }

    /**
     * ユーザー通知設定取得
     * @param $user_id
     * @return mixed
     */
    public function getDefaultReminder($user_id)
    {
        $user = User::where('id', $user_id)->first();
        $default_reminder = DefaultReminder::where('calendarlist_id', $user->calendarlist_id)->first();
        return $default_reminder;
    }
}
