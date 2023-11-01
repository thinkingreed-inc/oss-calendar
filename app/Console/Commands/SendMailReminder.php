<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Notifications\Notifiable;
use App\Notifications\Reminder;
use App\Models\Schedule;
use App\Models\Notification;
use App\Models\Recurring;
use Datetime;
use Carbon\Carbon;

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
        $nowDatetime = date('Y-m-d H:i:s');

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

        // 既に過ぎたスケジュールは除く
        $query->where('schedules.start_date', '>', $nowDatetime);

        // スケジュールの通知時間(スタート時間 - 通知設定(分))が 現在時刻以前のデータを取得
        $query->where(\DB::raw("DATE_SUB(schedules.start_date, INTERVAL reminders_overrides.overrides_minutes MINUTE)"), '<=', $nowDatetime);
        $query->orderBy('schedules.id', 'asc');
        $query->orderBy('users.id', 'asc');
        $query->distinct(
            'schedules.*',
            'users.*'
        );

        $notifications = $query->get();

        $activeStartFormatted = new DateTime($nowDatetime);
        $activeStartFormatted->format('Y-m-d');
        $activeEndFormatted = new DateTime($nowDatetime);
        $activeEndFormatted->format('Y-m-d');

        //繰り返し予定の抽出
        $recurrings = Recurring::with(['schedule','schedule.attendees'])
        ->join('schedules', 'recurrings.schedule_id', '=', 'schedules.id')
        ->where('recurrings.deleted','=',false)
        ->where('recurrings.start_date', '<=', $nowDatetime)
        // カレンダーの開始日から予定の期間（日）を引いた日付が終了日以下の場合
        ->whereRaw(
            'recurrings.end_date >= DATE_SUB(?, INTERVAL TIMESTAMPDIFF(DAY, schedules.start_date, schedules.end_date) DAY)', 
            [$activeStartFormatted]
        )
        ->get();

        $transformer = new \Recurr\Transformer\ArrayTransformer();
        $transformerConfig = new \Recurr\Transformer\ArrayTransformerConfig();
        $transformerConfig->enableLastDayOfMonthFix();
        $transformer->setConfig($transformerConfig);

        //繰り返し予定をRRuleに従い展開
        foreach ($recurrings as $key => $recurring){
            $schedule = $recurring->schedule;

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
                $nowDate = date('Y-m-d');
                $join->where('notifications.created_at', '>=', $nowDate.' 00:00:00');
                $join->where('notifications.created_at', '<=', $nowDate.' 23:59:59');
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

            $query->where('schedules.id', '=', $schedule->id);
    
            // スケジュールの通知時間(スタート時間 - 通知設定(分))が 現在時刻以前のデータを取得
            $query->orderBy('schedules.id', 'asc');
            $query->orderBy('users.id', 'asc');
            $query->distinct(
                'schedules.*',
                'users.*'
            );

            $temp_schedules = $query->get();
            // $this->output->writeln(print_r($temp_schedules,true));
            $activeStartFormatted = new DateTime($nowDatetime);
            $activeStartFormatted = $activeStartFormatted->format('Y-m-d');

            foreach($temp_schedules as $temp_schedule){
                $rule = new \Recurr\Rule($recurring->frequency);
                $recur_dates =$transformer->transform($rule);

                foreach ($recur_dates as $recur_date) {

                    $startdate =Carbon::createFromFormat('Y-m-d H:i:s', $recur_date->getStart()->format('Y-m-d H:i:s'));

                    if(!$startdate->isToday()) {
                        continue;
                    }

                    $temp_schedule->start_date = $startdate;
                    $temp_schedule->end_date = $recur_date->getEnd()->format('Y-m-d H:i:s');

                    $override_minutes = $temp_schedule->overrides_minutes;
                    $overrideNowDatetime = date('Y-m-d H:i:s', strtotime($nowDatetime.' +'.$override_minutes.' minutes'));

                    $this->output->writeln(print_r($temp_schedule,true));
                    $this->output->writeln($nowDatetime);
                    $this->output->writeln($activeStartFormatted.' 23:59:59');
                    $this->output->writeln($overrideNowDatetime);
                    $this->output->writeln($temp_schedule->start_date);

                    // 過去のスケジュールは送信しない
                    if(($startdate < Carbon::createFromFormat('Y-m-d H:i:s', $nowDatetime))){
                        continue;
                    }
                    // 本日のスケジュール以外の未来のスケジュールは送信しない
                    if(($startdate > Carbon::createFromFormat('Y-m-d H:i:s', $activeStartFormatted.' 23:59:59'))){
                        continue;
                    }
                    // 現在時刻にoverride_minitesを足した時間がスケジュール開始時間に到達していない場合は送信しない
                    if(($startdate > Carbon::createFromFormat('Y-m-d H:i:s', $overrideNowDatetime))){
                        continue;
                    }
                    $notifications->push($temp_schedule);
                }
            }
        }

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
