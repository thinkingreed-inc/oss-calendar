<?php

namespace App\Http\Controllers;

use App\Http\Requests\Schedule\ScheduleStoreRequest;
use App\Models\RemindersOverride;
use App\Models\Attendee;
use App\Models\EventType;
use App\Models\Schedule;
use App\Models\Recurring;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use DateTime;
class ScheduleController extends Controller
{
    protected $user;

    public function __construct(UserController $user)
    {
        $this->user = $user;
    }

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $my = \Auth::user();
        $viewType = $request->get("viewType");
        $activeStart = $request->get("activeStart");
        $activeEnd = $request->get("activeEnd");
        $select_group = $request->get("selectedGroup");

        // 表示グループ所属ユーザー取得
        $user_ids = $this->getGroupUsers($select_group, $my);

        // スケジュール取得
        $schedules = $this->getSchedules($viewType, $activeStart, $activeEnd, $user_ids, $my);
        return $schedules;
    }

    /**
     * @param $attendees
     * @param $calendarlist_id
     * @param $user_ids
     * @return boolean
     */
    private function checkGridEvent($attendees,$calendarlist_id,$user_ids){
        //Grid表示ではcalendarlist_idかattendeesのとuser_idsに被りがある場合抽出
        //user_idsのユーザーの予定データか？ またはオーナーか？
        if(in_array($calendarlist_id,$user_ids)){
            return true;
        }
        foreach ($attendees as $key => $attendee){
            if(in_array($attendee->attendee_id,$user_ids)){
                return true;
            }
        }
        return false;
    }

    /**
     * @param $schedule
     * @param $viewType
     * @param $user_ids
     * @param $my
     * @return integer
     */
    private function checkEvent($schedule,$viewType,$user_ids,$my){
        $attendees = $schedule->attendees;
        $calendarlist_id = $schedule->calendarlist_id;
        //Grid表示の際のフィルター
        if( $viewType == "dayGridMonth" || $viewType == "timeGridWeek" || $viewType == "timeGridDay") {
            if(!$this->checkGridEvent($attendees,$calendarlist_id,$user_ids)){
                return 0;
            }
        }
        foreach ($attendees as $key => $attendee){
            //自分自身の予定データか？ または自分自身がオーナーか？
            if($attendee->user_id == $my->id || $schedule->calendarlist_id == $my->calendarlist_id){
                return 1;
            }
            //共有グループに含まれているユーザーの予定
            if(in_array($attendee->user_id, $user_ids)){
                // 一般公開の予定
                if($schedule->visibility_id == "1"){
                    return 1;
                }
                // 予定あり表示の予定
                if($schedule->visibility_id == "2" && $schedule->public_setting_id == "2"){
                    return 2;
                }
                // 限定公開の予定
                if($schedule->visibility_id == "2" && $schedule->public_setting_id == "1"){
                    return 0;
                }
            }
        }
    }

    /**
     * @param $viewType
     * @param $activeStart
     * @param $activeEnd
     * @param $user_ids
     * @param $my
     * @return array
     */
    private function getSchedules($viewType, $activeStart, $activeEnd, $user_ids, $my){
        //繰り返しのためのRRuleの設定
        $transformer = new \Recurr\Transformer\ArrayTransformer();
        $transformerConfig = new \Recurr\Transformer\ArrayTransformerConfig();
        $transformerConfig->enableLastDayOfMonthFix();
        $transformer->setConfig($transformerConfig);

        $event_arrays= [];

        //繰り返し予定の抽出
        $recurrings = Recurring::with(['schedule','schedule.attendees'])
            ->join('schedules', 'recurrings.schedule_id', '=', 'schedules.id')
            ->where('recurrings.deleted','=',false)
            ->where('recurrings.start_date', '<=', $activeEnd)
            ->where('recurrings.end_date', '>=', \DB::raw("DATE_SUB('".$activeStart."', INTERVAL TIMESTAMPDIFF(DAY,schedules.start_date,schedules.end_date) DAY)"))// カレンダーの開始日から予定の期間（日）を引いた日付が終了日以下の場合
            ->get();

        $activeStartFormatted = new DateTime($activeStart);
        $activeStartFormatted->format('Y-m-d');
        $activeEndFormatted = new DateTime($activeEnd);
        $activeEndFormatted->format('Y-m-d');

        //繰り返し予定をRRuleに従い展開
        //parent_idとparent_uidを割り振り3次元配列に格納
        foreach ($recurrings as $key => $recurring){
            $schedule = $recurring->schedule;
            $isEventAllowed = $this->checkEvent($schedule,$viewType,$user_ids,$my);
            if($isEventAllowed){
                $parentID = $schedule->parent_id;
                $parentUID = $schedule->parent_uid;
                if (!$parentID) {
                    $parentID = $schedule->id;
                }
                if (!$parentUID) {
                    $parentUID = 1;
                }
                $rule = new \Recurr\Rule($recurring->frequency);
                $recur_dates =$transformer->transform($rule);
                $temp_schedule = $schedule;
                foreach ($recur_dates as $recur_date) {
                    $temp_schedule->start_date =$recur_date->getStart()->format('Y-m-d H:i:s');
                    $temp_schedule->end_date =$recur_date->getEnd()->format('Y-m-d H:i:s');
                    // 繰り返しスケジュールのうち、表示に関係ない日付のデータを削除
                    if(($temp_schedule->start_date > $activeEndFormatted)){
                        continue;
                    }
                    if(($temp_schedule->end_date < $activeStartFormatted)){
                        continue;
                    }
                    $temp_schedule->parent_id = $parentID;
                    $temp_schedule->parent_uid = $parentUID;
                    $event_arrays[$temp_schedule->event_type_id][$parentID][$parentUID] = [
                            'event' => clone $temp_schedule,
                            'is_event_allowed' => ($isEventAllowed - 1)
                    ];
                    $parentUID += 1;
                }
            }
        }

        //繰り返しでない予定の抽出
        //parent_idとparent_uidに従い3次元配列に格納
        $schedules = Schedule::with('attendees')
            ->where('recurring','=', false)
            ->where('start_date', '<=', $activeEnd)
            ->where('end_date', '>=', $activeStart)
            ->get();
        foreach ($schedules as $key => $schedule) {
            $isEventAllowed = $this->checkEvent($schedule, $viewType, $user_ids, $my);
            if($isEventAllowed) {
                $parentID = $schedule->parent_id;
                $parentUID = $schedule->parent_uid;
                if (!$parentID) {
                    $parentID = $schedule->id;
                }
                if (!$parentUID) {
                    $parentUID = 1;
                }
                if (!$schedule->deleted) {
                    $schedule->parent_id = $parentID;
                    $schedule->parent_uid = $parentUID;
                    $event_arrays[$schedule->event_type_id][$parentID][$parentUID] = [
                        'event' => clone $schedule,
                        'is_event_allowed' => ($isEventAllowed - 1)
                    ];
                } else {
                    unset($event_arrays[$schedule->event_type_id][$parentID][$parentUID]);
                }
            }
        }

        //fullcalendar用にデータをセット
        $eventSources = [];
        foreach ($event_arrays as $event_type_id => $event_array){
            $eventSource = [];
            $eventSource["color"] = EventType::find($event_type_id) == null ? "#FFFFFF" : EventType::find($event_type_id)->color; // 予定タイプが存在しない場合、白色にする。
            $eventSource["textColor"] = $this->getBlackOrWhiteFromBackgroundColor($eventSource["color"]);//"white";
            foreach ($event_array as $parent_id => $event_pairs){
                foreach ($event_pairs as $parent_uid => $event_pair) {
                    $schedule = $event_pair['event'];
                    $is_event_allowed = $event_pair['is_event_allowed'];
                    //TimeLine表示の場合は参加ユーザーをベースにexplode
                    $attendees = $schedule->attendees;
                    if( $viewType == "dayGridMonth" || $viewType == "timeGridWeek" || $viewType == "timeGridDay") {
                        $event = $this->setEvent($schedule, $attendees[0], $is_event_allowed);
                        $eventSource["events"][] = $event;
                    }
                    else{
                        foreach ($attendees as $key => $attendee){
                            $event = $this->setEvent($schedule, $attendee, $is_event_allowed);
                            $eventSource["events"][] = $event;
                        }
                    }
                }
            }
            $eventSources[] = $eventSource;
        }

        // ユーザー情報セット
        $users = User::where("users.is_enable", "=", true)
            ->whereIn("users.id", $user_ids)
            ->get();
        $resources = [];
        foreach($users as $user) {
            $resource = [];
            $resource["id"] = $user->id;
            $resource["title"] = $user->lastname . " " . $user->firstname;
            $resources[] = $resource;
        }
        return ["eventSources" => $eventSources, "resources" => $resources];
    }
    /**
     * @param $schedule
     * @param $attendee
     * @param $setMyEvent
     * @return array
     */
    private function setEvent($schedule, $attendee, $setMyEvent){
        // 予定セット
        $event = [];
        $event["resourceId"] = $attendee->user_id;
        $event["title"] = $this->getEventTitle($schedule, $setMyEvent);
        $event["id"] = $schedule->id. '_' . $schedule->parent_id . '_' . $schedule->parent_uid;
        $event["event_type_id"] = $schedule->event_type_id;
        $allday = $schedule->allday;
        $event["allday"] = $allday;
        if ($allday) {
            $event["start"] = $schedule->start_date->format('Y-m-d');
            $event["end"] = $schedule->end_date->format('Y-m-d');
        } else {
            $event["start"] = $schedule->start_date->format('Y-m-d') . "T" . $schedule->start_date->format('H:i:s');
            $event["end"] = $schedule->end_date->format('Y-m-d') . "T" . $schedule->end_date->format('H:i:s');
        }
        return $event;
    }

    /**
     * @param $schedule
     * @param $setMyEvent
     * @return string
     */
    private function getEventTitle($schedule, $setMyEvent){
        // 表示タイトル取得
        $title = $schedule->summary;
        if(!$setMyEvent) {
            if ($schedule->visibility_id == "2" && $schedule->public_setting_id == "2") {
                // 限定公開(予定あり)
                $title = "予定あり";
            }
        }
        return $title;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ScheduleStoreRequest $request)
    {
        //
        return $this->save($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id){
        [$id,$parent_id,$parent_uid] = explode("_", $id);
        $schedule = Schedule::find($id);
        $result = $this->showRecurrenceOverride($id,$parent_id,$parent_uid,$schedule);
        $result = $this->showAttendee($schedule->attendees, $result);
        $result = $this->showRemindersOverride($schedule->id, $result);
        return $result;
    }
    /**
     * parent_idとparent_uidの情報を元に繰り返し予定の時刻を訂正
     *
     * @param null $id
     * @param null $parent_id
     * @param null $parent_uid
     * @param \App\Models\Schedule $schedule
     * @return array
     */
    private function showRecurrenceOverride($id,$parent_id,$parent_uid,$schedule){
        if($schedule->recurring){
            $transformer = new \Recurr\Transformer\ArrayTransformer();
            $transformerConfig = new \Recurr\Transformer\ArrayTransformerConfig();
            $transformerConfig->enableLastDayOfMonthFix();
            $transformer->setConfig($transformerConfig);
            $recurring = $schedule->recurrence;
            $rule = new \Recurr\Rule($recurring->frequency);
        }
        if($schedule->parent_id && $parent_id == $schedule->parent_id && $schedule->parent_uid && $schedule->parent_uid != $parent_uid){
            $recur_dates =$transformer->transform($rule);
            $temp_uid = $schedule->parent_uid;
            foreach($recur_dates as $recur_date){
                if($temp_uid == $parent_uid){
                    $schedule->start_date = $recur_date->getStart()->format('Y-m-d H:i:s');
                    $schedule->end_date = $recur_date->getEnd()->format('Y-m-d H:i:s');
                    break;
                }
                $temp_uid += 1;
            }
        }

        $result = $schedule->toArray();
        $result["id"] = $id. '_' . $parent_id . '_' . $parent_uid;

        if($schedule->recurring){
            $freq = $rule->getFreq();
            switch ($freq) {
                case \Recurr\Frequency::YEARLY:
                    $freq = 'YEARLY';
                    break;
                case \Recurr\Frequency::MONTHLY:
                    $freq = 'MONTHLY';
                    break;
                case \Recurr\Frequency::WEEKLY:
                    $freq = 'WEEKLY';
                    break;
                case \Recurr\Frequency::DAILY:
                    $freq = 'DAILY';
                    break;
            }
            $result["recurrence"] = [
                "recurrence_id" => 2,
                "recurrence_interval" => $rule->getInterval(),
                "recurrence_unit" => $freq,
                "start_start_date" => $rule->getStartDate()->format('Y-m-d'),
                "start_end_date" => $rule->getEndDate()->format('Y-m-d'),
                "end_date" => $rule->getUntil()->format('Y-m-d')
            ];
        }
        else{
            $result["recurrence"] = [
                "recurrence_id" => 1,
                "recurrence_interval" => 1,
                "recurrence_unit" => "DAILY",
                "start_start_date" => "",
                "start_end_date" => "",
                "end_date" => ""
            ];
        }
        return $result;
    }
    private function showAttendee($attendees, $result){
        foreach($attendees as $attendee) {
            if(!empty($attendee->user)){
                $user["value"] = $attendee->user->id;
                $user["text"] = $attendee->user->lastname . " " . $attendee->user->firstname;
                $result["users"][] = $user;
            }
        }
        return $result;
    }

    private function showRemindersOverride($schedule_id, $result){
        $query = RemindersOverride::select('overrides_minutes as reminder_minutes');
        $query->where('schedule_id', '=', $schedule_id);
        $reminder = $query->distinct('overrides_minutes')->first();
        if(!empty($reminder)){
            $result["reminder"] = 1;
            $result["reminder_minutes"] = $reminder->reminder_minutes;
        }else{
            $result["reminder"] = 0;
            $result["reminder_minutes"] = null;
        }
        return $result;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ScheduleStoreRequest $request, $id){
        [$id,$parent_id,$parent_uid] = explode("_", $id);
        //繰り返し1日のみの編集のフラグ管理
        $one_day_edit = $request["one_day_edit"];
        unset($request["one_day_edit"]);
        return $this->save($request, $id, $parent_id, $parent_uid, $one_day_edit);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id){
        // スケジュール削除
        [$id,$parent_id,$parent_uid] = explode("_", $id);
        Schedule::find($id)->update(['deleted' => 1]);
        Recurring::where('schedule_id', '=', $id)->update(['deleted' => 1]);
    }

    /**
     * ドラックアンドドロップやリサイズしたときのスケジュール更新
     *
     * @param Request $request
     * @param string $id
     */
    public function drop(Request $request, $id){
        [$id,$parent_id,$parent_uid] = explode("_", $id);

        // スケジュール更新
        $temp_schedule = Schedule::find($id);
        $recurring = $temp_schedule->recurring;

        //繰り返し予定かどうか
        if($recurring){
            $schedule = $temp_schedule->replicate();
            $schedule->recurring = false;
            $schedule->recurring_id = null;
            $schedule->parent_id = $parent_id;
            $schedule->parent_uid = $parent_uid;
            $schedule->deleted = false;
        }
        else{
            $schedule = $temp_schedule;
        }

        //新しい時刻への更新
        $allDay = $request->get("allDay");
        $schedule->allday = $allDay;
        if($allDay) {
            $schedule->start_date = $request->get("startDate");
            $schedule->end_date = $request->get("endDate");
        }
        else {
            $start_date = $request->get("startDate")." ".$request->get("startTime");
            $end_date = $request->get("endDate")." ".$request->get("endTime");
            $schedule->start_date = Carbon::createFromFormat('Y-m-d H:i', $start_date, \Config::get("app.timezone", 'Asia/Tokyo'))->toDateTimeString();
            $schedule->end_date = Carbon::createFromFormat('Y-m-d H:i', $end_date, \Config::get("app.timezone", 'Asia/Tokyo'))->toDateTimeString();
        }
        $schedule->save();

        //繰り返し予定場合の参加者とリマインダの引き継ぎ
        if($recurring){
            $new_schedule_id = $schedule->id;
            $attendees = Attendee::where("schedule_id" , "=", $id)->get();
            foreach ($attendees as $attendee){
                $new_attendee = $attendee->replicate();
                $new_attendee->schedule_id = $new_schedule_id;
                $new_attendee->save();
            }
            $reminders = RemindersOverride::where("schedule_id" , "=", $id)->get();
            foreach ($reminders as $reminder){
                $new_reminder = $reminder->replicate();
                $new_reminder->schedule_id = $new_schedule_id;
                $new_reminder->save();
            }
        }

        //予定がユーザー間で移動する場合の参加者の変更処理
        $eventType = $request->get("eventType");
        if($eventType == "changeUser") {
            // スケジュール参加者変更
            $newUserId = $request->get("newUserId");
            $oldUserId = $request->get("oldUserId");
            if($recurring){
                $new_schedule_id = $schedule->id;
                $attendee = Attendee::where("schedule_id" , "=", $new_schedule_id)->where("user_id", "=", $oldUserId)->first();
            }
            else {
                $attendee = Attendee::where("schedule_id" , "=", $id)->where("user_id", "=", $oldUserId)->first();
            }
            $attendee->user_id = $newUserId;
            $attendee->save();
        }

    }

    /**
     * @param \Illuminate\Http\Request  $request
     * @param null $id
     * @param null $parent_id
     * @param null $parent_uid
     * @param boolean $one_day_edit
     * @return mixed
     */
    private function save(Request $request, $id = null, $parent_id = null, $parent_uid = null, $one_day_edit = false){
        //
        $my = \Auth::user();
        //新規登録 or 1日変更
        if($id == null || $one_day_edit) {
            $schedule = new Schedule();
        }
        //編集登録
        else {
            $schedule = Schedule::find($id);
            //以前の繰り返しを削除する
            $this->destroyRecurrence($id);
            // 以前のアラームを削除する
            $this->destroyRemindersOverride($id);
            // 以前の参加者を削除する
            $this->destroyAtendee($id);
        }
        $requestData = $request->all();

        $main_user_id = null;
        $main_calendarlist_id = null;

        $allday = $requestData["allday"];
        // 日付をDBの形式に合わせる
        if( !$allday ) {
            $requestData["start_date"] = $requestData["start_date"] . " " . $requestData["start_date_time"]. ":00" ;
            $requestData["end_date"] = $requestData["end_date"] . " " . $requestData["end_date_time"]. ":00" ;
        }

        // 終了日時が開始日時よりも前に来ていたら逆転させる
        $dtStart = new \DateTime($requestData['start_date']);
        $dtEnd = new \DateTime($requestData['end_date']);
        if($dtStart > $dtEnd) {
            $s = $requestData['start_date'];
            $e = $requestData['end_date'];
            $requestData['start_date'] = $e;
            $requestData['end_date'] = $s;
        }

        $schedule->fill($requestData);
        $schedule->calendarlist_id = $my->calendarlist_id;//登録した人のカレンダーリストIDを入れる
        if($one_day_edit){
            $schedule->parent_id = $parent_id;
            $schedule->parent_uid = $parent_uid;
        }
        $schedule->save();

        //繰り返し登録
        $this->saveRecurrence($requestData,$schedule);

        // アラーム更新
        $this->saveRemindersOverride($requestData, $schedule);

        // 参加者更新
        $this->saveAttendee($requestData, $my, $schedule);

        return $schedule;
    }


    //繰り返し登録
    private function saveRecurrence($requestData, $schedule){
        if($requestData["recurrence"]["recurrence_id"] != "1" && $requestData["recurrence"]){
            $timezone    = 'Asia/Tokyo';
            $startDate   = new \DateTime($schedule->start_date, new \DateTimeZone($timezone));
            $endDate     = new \DateTime($schedule->end_date, new \DateTimeZone($timezone));
            $freq = $requestData["recurrence"]["recurrence_unit"];
            $recur_interval = $requestData["recurrence"]["recurrence_interval"];
            $until_date = new \DateTime($requestData["recurrence"]["end_date"]. " 23:59:59");
            $rule = (new \Recurr\Rule)
                ->setTimezone($timezone)
                ->setStartDate($startDate,true)
                ->setEndDate($endDate)
                ->setFreq($freq)
                ->setInterval($recur_interval)
                ->setUntil($until_date)
            ;
            $recurrence = new Recurring;
            $recurrence->schedule_id = $schedule->id;
            $recurrence->frequency = $rule->getString();
            $recurrence->start_date = $startDate->format('Y-m-d');
            $recurrence->end_date = $until_date->format('Y-m-d');
            $recurrence->save();
            $schedule->update(['recurring' => 1,'recurring_id' => $recurrence->id, 'parent_id' => $schedule->id, 'parent_uid' => 1]);
        }
    }
    // アラーム更新
    private function saveRemindersOverride($requestData, $schedule){

        if($requestData["allday"] != "1" && $requestData["reminder"]){

            // 参加者に通知登録
            foreach($requestData["users"] as $userData) {
                $user = User::find($userData["value"]);
                $users[] = $user;
            }
            foreach($users as $user) {
                $reminder = new RemindersOverride;
                $reminder->schedule_id = $schedule->id;
                $reminder->user_id = $user->id;
                $reminder->overrides_method_id = \Config::get('const.REMINDER_EMAIL');
                $reminder->overrides_minutes = $requestData["reminder_minutes"];
                $reminder->save();
            }
        }

    }

    // 参加者更新
    private function saveAttendee($requestData, $my, $schedule){
        $users = [];
        foreach($requestData["users"] as $userData) {
            $user = User::find($userData["value"]);
            $users[] = $user;
        }

        foreach($users as $user) {
            $attendee = new Attendee();
            $attendee->schedule_id = $schedule->id;
            $attendee->user_id = $user->id;
            $attendee->attendee_id = $user->id;
            $attendee->email = $user->email;
            $attendee->display_name = $user->lastname . " " . $user->firstname;
            if($my->id == $user->id) {
                $attendee->organizer = true;
            }
            $attendee->save();
        }
    }

    // 繰り返し削除
    private function destroyRecurrence($id){
        Recurring::where('schedule_id', '=', $id)->delete();
    }

    // アラーム削除
    private function destroyRemindersOverride($id){
        RemindersOverride::where('schedule_id', '=', $id)->delete();
    }

    // 参加者削除
    private function destroyAtendee($id){
        Attendee::where("schedule_id", "=", $id)->delete();
    }

    private function getGroupUsers($select_group, $my){
        $user_ids = Array();

        // value値 = [model]:[id] の文字列形式　分割
        $json_data = json_decode($select_group)->value;
        $group_data = explode(":", $json_data);

        // ユーザー取得
        if(count($group_data) >= 2){
            $join_model = $group_data[0];
            $id = $group_data[1];

            $query = User::select('users.*');
            $query = $this->user->getJoinUserQuery($query, $join_model, $id);
            $query->orderBy('users.username', 'asc');
            $users = $query->get();

            // IDセット
            foreach ($users as $user){
                array_push($user_ids, $user->id);
            }
        }else{
            // IDセット
            array_push($user_ids, $my->id);
        }

        return $user_ids;
    }

    private function getBlackOrWhiteFromBackgroundColor ( $hexcolor ) {
        $black = '#4c4c4c';
        $white = '#ffffff';
        $r = hexdec(substr($hexcolor, 1, 2 )) ;
        $g = hexdec(substr($hexcolor, 3, 2 )) ;
        $b = hexdec(substr($hexcolor, 5, 2 )) ;
        return (((($r * 299)+($g * 587)+($b * 114))/1000) < 160) ? $white : $black ;
    }
}
