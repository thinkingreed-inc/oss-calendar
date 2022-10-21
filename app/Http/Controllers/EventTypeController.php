<?php

namespace App\Http\Controllers;

use App\Models\EventType;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\EventType\EventTypeStoreRequest;

class EventTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        //
        $uri = $request->path();

        $sortBy = $request->get("sortBy");
        $sortDesc = $request->get("sortDesc");
        $sortStr = "";

        if(!$sortBy){
            // 並び順が指定されていない場合、デフォルトは「並び順」項目の昇順で表示する。
            $sortBy = "rank";
            $sortDesc = "false";
        }

        if($sortDesc == "true") {
            $sortStr = "desc";
        }
        elseif($sortDesc == "false") {
            $sortStr = "asc";
        }
        $page = $request->get("page");
        $itemsPerPage = $request->get("itemsPerPage");
        $search = $request->get("search");

        $query = EventType::select('*');
        if( strlen($search) > 0 ) {
            $query->where("name", 'LIKE', "%{$search}%");
        }

        if (in_array('admin', explode('/', $uri))) {
            // 管理者の管理画面の場合
        } else {
            $query->where('event_types.is_enable', '=', 1);
        }

        if( strlen($sortBy) > 0 ) {
            $query->orderBy($sortBy, $sortStr);
        }
        if($itemsPerPage == '-1'){
            $itemsPerPage = 1000;
        }
        $event_types = $query->paginate($itemsPerPage, ['*'], 'page', $page);
        return $event_types;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param EventTypeStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(EventTypeStoreRequest $request)
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
        $event_type = EventType::where('id', $id)->first();

        return $event_type ?? abort(404);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param EventTypeStoreRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EventTypeStoreRequest $request, $id)
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
        EventType::destroy($id);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param null $id
     * @return mixed
     */
    private function save(Request $request, $id = null){

        if($id == null){
            // 予定タイプ登録
            $event_type = new EventType();
            $event_type->fill($request->all())->save();
            $event_type->save();
        }else{
            // 予定タイプ更新
            $event_type = EventType::where('id', $id)->first();
            $event_type->fill($request->all())->save();
        }

        return $event_type;
    }
}
