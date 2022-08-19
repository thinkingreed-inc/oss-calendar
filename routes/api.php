<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// ログイン
logger('test', [__FILE__ => __LINE__]);
Route::post('/auth/login', 'Auth\LoginController@login')->name("auth.login");


// パスワードリセット
Route::post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name("password.reset");
// パスワードリセット後の変更
Route::post('/password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');


/**
 * ログインユーザーに利用可能な機能
 */
Route::group(['middleware' => ['auth:api']], function () {
    // ログインしたユーザー情報を取得
    Route::get('/myself', 'UserController@myself')->name("myself.myself");
    // ログアウト
    Route::post('/auth/logout', 'Auth\LoginController@logout')->name("auth.logout.logout");

    /**
     * 管理者のみ利用可能な機能
     */
    Route::group(['middleware' => ['can:admin']], function () {
        // ユーザー管理
        Route::apiResource('/user/admin', 'UserController', ['as' => 'user']);
        // 部署マスタ
        Route::apiResource('/department/admin', 'DepartmentController', ['as' => 'department']);
        // 予定タイプマスタ
        Route::apiResource('/event_type/admin', 'EventTypeController', ['as' => 'event_type']);
        // 共有グループマスタ
        Route::apiResource('/common_group/admin', 'CommonGroupController', ['as' => 'common_group']);
        // 休祝日設定
        Route::apiResource('/holiday/admin', 'HolidayController', ['as' => 'holiday']);
        // 休祝日設定CSVアップロード
        Route::post('/holiday/admin/upload', 'HolidayController@upload')->name("holiday.admin.upload.upload");

        // パスワード更新
        Route::patch('/user/password/{id}', 'UserController@password')->name("user.password.id.password");
        // 有効無効更新
        Route::patch('/enable_disable/{modelName}/{id}', 'Controller@enableDisable')->name("enable_disable.modulename.id.enabledisable");
    });

    // ユーザー取得
    Route::get('/user', 'UserController@index')->name("user.index");
    // 部署マスタ取得
    Route::get('/department', 'DepartmentController@index')->name("department.index");
    // 共有グループ取得
    Route::get('/common_group', 'CommonGroupController@index')->name("common_group.index");
    // 休祝日設定取得
    Route::get('/holiday/event', 'HolidayController@getEvent')->name("holiday.event.getevent");

    /**
     * マイページの処理
     */
    // パスワード更新
    Route::patch('/mypage/password', 'MypageController@password')->name("mypage.password.password");
    // メールアドレス変更
    Route::patch('/mypage/email/edit', 'MypageController@sendChangeLinkEmail')->name("mypage.email.edit.sendchangelinkemail");
    // メールアドレス変更最終確認
    Route::patch('/mypage/email/check', 'MypageController@checkEmail')->name("mypage.email.check.checkemail");
    // メールアドレス再送
    Route::patch('/mypage/email/resend', 'MypageController@resend')->name("mypage.email.resend.resend");
    // 確認メールの取消
    Route::delete('/mypage/email/cancel', 'MypageController@cancel')->name("mypage.email.cancel.cancel");
    // 確認メールのメールアドレス取得
    Route::get('/mypage/email/confirm', 'MypageController@getConfirmEmail')->name("mypage.email.confirm.getconfirmemail");
    // 個別グループ
    Route::apiResource('/individual_group', 'IndividualGroupController');
    // 個人設定
    Route::patch('/mypage/setting', 'MypageController@setting')->name("mypage.setting.setting");
    // 通知設定取得
    Route::get('/mypage/reminder/{user_id}', 'MypageController@getDefaultReminder')->name("mypage.reminder.user_id.getdefaultreminder");

    /**
     * スケジュールポップアップ
     */
    Route::get('/user/two_select/{group_id}', 'UserController@getUsersForTwoSelect')->name("user.two_select.group_id.getusersfortwoselet");
    Route::get('/department/two_select', 'DepartmentController@getDepartmentsForTwoSelect')->name("department.two_select.getDepartmentsfortwoselect");
    Route::get('/event_type', 'EventTypeController@index')->name("event_type.index");

    /**
     * スケジュール関連
     */
    // スケジュール登録
    Route::post('/schedule', 'ScheduleController@store')->name("schedule.store");
    // スケジュール更新
    Route::put('/schedule/{schedule_id}', 'ScheduleController@update')->name("schedule.schedule_id.update");
    // スケジュールのドラックアンドドロップやリサイズ更新
    Route::patch('/schedule/drop/{schedule_id}', 'ScheduleController@drop')->name("schedule.drop.schedule_id.drop");
    // スケジュール削除
    Route::delete('/schedule/{schedule_id}', 'ScheduleController@destroy')->name("schedule.schedule_id.destroy");
    // 表示データ取得
    Route::get('/schedule', 'ScheduleController@index')->name("schedule.index");
    // 指定したスケジュール表示
    Route::get('/schedule/{schedule_id}', 'ScheduleController@show')->name("schedule.schedule_id.show");
});
