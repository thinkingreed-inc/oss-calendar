<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;
use App\Http\Requests\Holiday\HolidayStoreRequest;

class HolidayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $sortBy = $request->get("sortBy");
        $sortDesc = $request->get("sortDesc");
        if($sortDesc == "true") {
            $sortStr = "desc";
        }
        elseif($sortDesc == "false") {
            $sortStr = "asc";
        }
        $page = $request->get("page");
        $itemsPerPage = $request->get("itemsPerPage");
        $search = $request->get("search");

        $query = Holiday::select('*');
        if( strlen($search) > 0 ) {
            $query->where(\DB::raw("CONCAT(summary, ',', holiday)"), 'LIKE', "%{$search}%");
        }
        $holidayYear = $request->get("holiday_year");
        if(!empty($holidayYear)) {
            $query->where('holiday', '>=', $holidayYear."/01/01");
            $query->where('holiday', '<=', $holidayYear."/12/31");
        }
        if( strlen($sortBy) > 0 ) {
            $query->orderBy($sortBy, $sortStr);
        }
        $holidays = $query->paginate($itemsPerPage, ['*'], 'page', $page);
        return $holidays;
    }

    public function getEvent(Request $request){
        $activeStart = $request->get("activeStart");
        $activeEnd = $request->get("activeEnd");

        $query = Holiday::select('*');
        $query->where('is_enable', '=', 1);
        $query->where('holiday', '>=', $activeStart);
        $query->where('holiday', '<=', $activeEnd);
        $holidays = $query->get();

        $eventSources = [];
        foreach($holidays as $holiday){
            $event = [];
            $event[0]["resourceId"] = 0;
            $event[0]["title"] = $holiday->summary;
            $event[0]["id"] = 0;
            $event[0]["event_type_id"] = 0;
            $event[0]["start"] = $holiday->holiday;
            $event[0]["end"] = $holiday->holiday;

            $eventSource = [];
            $eventSource["editable"] = false;
            $eventSource["events"] = $event;
            $eventSource["color"] = \Config::get('const.HOLIDAY_EVENT_COLOR');
            $eventSource["textColor"] = \Config::get('const.HOLIDAY_TEXT_COLOR');
            $eventSources[] = $eventSource;
        }
        return $eventSources;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param HolidayStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(HolidayStoreRequest $request)
    {
        return $this->save($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $holiday = Holiday::where('id', $id)->first();

        return $holiday ?? abort(404);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param HolidayStoreRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(HolidayStoreRequest $request, $id)
    {
        return $this->save($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        Holiday::destroy($id);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param null $id
     * @return mixed
     */
    private function save(Request $request, $id = null){

        if($id == null){
            // 休祝日登録
            $holiday = new Holiday();
            $holiday->fill($request->all())->save();
            $holiday->save();
        }else{
            // 休祝日更新
            $holiday = Holiday::where('id', $id)->first();
            $holiday->fill($request->all())->save();
        }

        return $holiday;
    }

    /**
     * アップロード処理
     * (1項目：休日名、2項目：日付)
     * @param Request $request
     */
    public function upload(Request $request){

        // CSV読み込み
        $file = $request->file('holiday_files');

        // csv validation
        $this->validateCSV($request);

        // CSVファイル内データ取得
        $content = file_get_contents($file->getRealPath());
        $content = mb_convert_encoding($content, "UTF8", "SJIS-WIN");
        $contents = explode("\n", $content);
        foreach($contents as $content){
            $vals = explode(",", $content);
            // 項目が一致しない場合は、無視する
            if(count($vals) < \Config::get('const.HOLIDAY_CSV_COLUMNS')) continue;

            // 休祝日データ取得
            $summary = $vals[0];
            $holiday = $vals[1];
            $rank = $vals[2];

            // 休祝日登録
            $model = new Holiday();
            $model->holiday = $holiday;
            $model->summary = $summary;
            $model->rank = $rank;
            $model->save();
        }
    }

    private function validateCSV($request){
        $this->validate($request, [
            'file' => [
                'file',
                'csv',
                'mimes:csv,txt',
                'mimetypes:text/plain',
            ]
        ]);
    }
}
