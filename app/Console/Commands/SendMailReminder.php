<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Notifications\Notifiable;
use App\Notifications\Reminder;
use App\Models\Schedule;
use App\Models\Notification;

class SendMailReminder extends Command
{
    use Notifiable;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command reminder';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // 通知データ取得
        $notifications = $this->getScheduleReminders();

        foreach ($notifications as $notification) {

            // メール送信
            $this->sendMail($notification);

            // メール送信済み更新
            $this->saveNotification($notification);
        }

    }

    private function getScheduleReminders(){

        $query = Schedule::select(
            'schedules.*',
            'users.*',
            'reminders_overrides.*',
            'schedules.id AS schedule_id',
            'users.id AS user_id'
        );
        // join 参加者TB
        $query = $query->Join("attendees","attendees.schedule_id","=", "schedules.id");
        // join ユーザーMST
        $query = $query->Join("users","users.id", "=","attendees.user_id");
        // join 通知設定TB
        $query = $query->Join("reminders_overrides", function ($join) {
            $join->on('reminders_overrides.schedule_id', '=', 'schedules.id');
            $join->on('reminders_overrides.user_id', '=', 'users.id');
        });
        // left join 送信済みTB
        $query = $query->leftJoin("notifications", function ($join) {
            $join->on('notifications.schedule_id', '=', 'schedules.id');
            $join->on('notifications.user_id', '=', 'users.id');
        });

        // 通知設定が有効
        $query->where('reminders_overrides.overrides_method_id', '=', 1);
        // 送信日時(作成日)がNull => 通知 未送信
        $query->where('notifications.created_at', '=', null);
        // 通知先ユーザーが有効／未削除
        $query->where('users.is_enable', '=', 1);
        $query->where('users.deleted_at', '=', null);

        // スケジュールが削除されていない
        $query->where('schedules.deleted', '=', 0);

        // スケジュールの通知時間(スタート時間 - 通知設定(分))が 現在時刻以前のデータを取得
        $nowDatetime = date('Y-m-d H:i:s');
        $query->where(\DB::raw("DATE_SUB(schedules.start_date, INTERVAL reminders_overrides.overrides_minutes MINUTE)"), '<=', $nowDatetime);
        $query->orderBy('schedules.id', 'asc');
        $query->orderBy('users.id', 'asc');
        $query->distinct(
            'schedules.*',
            'users.*'
        );

        $notifications = $query->get();

        return $notifications;
    }

    private function sendMail($notification){
        $options = [
            "summary" => $notification->summary,
            "start_date" => $notification->start_date,
            "end_date" => $notification->end_date,
            "location" => $notification->location,
            "description" => $notification->description,
            "overrides_minutes" => $notification->overrides_minutes,
        ];

        $this->email = $notification->email;
        $this->notify(new Reminder($options));
    }

    private function saveNotification($notification){

        $saveModel = new Notification();
        $saveModel->schedule_id = $notification->schedule_id;
        $saveModel->user_id = $notification->user_id;
        $saveModel->save();

    }
}
