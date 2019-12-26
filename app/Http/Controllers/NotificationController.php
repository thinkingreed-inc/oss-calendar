<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Requests\Notification\NotificationStoreRequest;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $notifications = Notification::select('*')->get();
        return $notifications;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param NotificationStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(NotificationStoreRequest $request)
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
        $notification = Notification::where('id', $id)->first();
        return $notification ?? abort(404);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param NotificationStoreRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(NotificationStoreRequest $request, $id)
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
        Notification::destroy($id);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param null $id
     * @return mixed
     */
    private function save(Request $request, $id = null){

        if($id == null){
            // 登録
            $notification = new Notification();
            $notification->fill($request->all())->save();
            $notification->save();
        }else{
            // 更新
            $notification = Notification::where('id', $id)->first();
            $notification->fill($request->all())->save();
        }

        return $notification;
    }

}
