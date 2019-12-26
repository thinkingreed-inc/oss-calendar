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
Route::post('/auth/login', 'Auth\LoginController@login');

// パスワードリセット
Route::post('/password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name("password.reset");
// パスワードリセット後の変更
Route::post('/password/reset', 'Auth\ResetPasswordController@reset')->name('password.update');


/**
 * ログインユーザーに利用可能な機能
 */
Route::group(['middleware' => ['auth:api']], function () {
    // ログインしたユーザー情報を取得
    Route::get('/myself', 'UserController@myself');
    // ログアウト
    Route::post('/auth/logout', 'Auth\LoginController@logout');

    /**
     * 管理者のみ利用可能な機能
     */
    Route::group(['middleware' => ['can:admin']], function () {
        // ユーザー管理
        Route::apiResource('/user/admin', 'UserController');
        // 部署マスタ
        Route::apiResource('/department/admin', 'DepartmentController');
        // 予定タイプマスタ
        Route::apiResource('/event_type/admin', 'EventTypeController');
        // 共有グループマスタ
        Route::apiResource('/common_group/admin', 'CommonGroupController');
        // 休祝日設定
        Route::apiResource('/holiday/admin', 'HolidayController');
        // 休祝日設定CSVアップロード
        Route::post('/holiday/admin/upload', 'HolidayController@upload');

        // パスワード更新
        Route::patch('/user/password/{id}', 'UserController@password');
        // 有効無効更新
        Route::patch('/enable_disable/{modelName}/{id}', 'Controller@enableDisable');
    });

    // ユーザー取得
    Route::get('/user', 'UserController@index');
    // 部署マスタ取得
    Route::get('/department', 'DepartmentController@index');
    // 共有グループ取得
    Route::get('/common_group', 'CommonGroupController@index');
    // 休祝日設定取得
    Route::get('/holiday/event', 'HolidayController@getEvent');

    /**
     * マイページの処理
     */
    // パスワード更新
    Route::patch('/mypage/password', 'MypageController@password');
    // メールアドレス変更
    Route::patch('/mypage/email/edit', 'MypageController@sendChangeLinkEmail');
    // メールアドレス変更最終確認
    Route::patch('/mypage/email/check', 'MypageController@checkEmail');
    // メールアドレス再送
    Route::patch('/mypage/email/resend', 'MypageController@resend');
    // 確認メールの取消
    Route::delete('/mypage/email/cancel', 'MypageController@cancel');
    // 確認メールのメールアドレス取得
    Route::get('/mypage/email/confirm', 'MypageController@getConfirmEmail');
    // 個別グループ
    Route::apiResource('/individual_group', 'IndividualGroupController');
    // 個人設定
    Route::patch('/mypage/setting', 'MypageController@setting');
    // 通知設定取得
    Route::get('/mypage/reminder/{user_id}', 'MypageController@getDefaultReminder');

    /**
     * スケジュールポップアップ
     */
    Route::get('/user/two_select/{group_id}', 'UserController@getUsersForTwoSelect');
    Route::get('/department/two_select', 'DepartmentController@getDepartmentsForTwoSelect');
    Route::get('/event_type', 'EventTypeController@index');

    /**
     * スケジュール関連
     */
    // スケジュール登録
    Route::post('/schedule', 'ScheduleController@store');
    // スケジュール更新
    Route::put('/schedule/{schedule_id}', 'ScheduleController@update');
    // スケジュールのドラックアンドドロップやリサイズ更新
    Route::patch('/schedule/drop/{schedule_id}', 'ScheduleController@drop');
    // スケジュール削除
    Route::delete('/schedule/{schedule_id}', 'ScheduleController@destroy');
    // 表示データ取得
    Route::get('/schedule', 'ScheduleController@index');
    // 指定したスケジュール表示
    Route::get('/schedule/{schedule_id}', 'ScheduleController@show');
});
