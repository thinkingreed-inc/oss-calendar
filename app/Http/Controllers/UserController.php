<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\BelongCommonGroup;
use App\Models\BelongDepartment;
use App\Models\Calendarlist;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\User\PasswordUpdateRequest;
use App\Http\Requests\User\UserStoreRequest;

class UserController extends Controller
{
    protected $mypage;

    public function __construct(MypageController $mypage)
    {
        $this->mypage = $mypage;
    }

    //
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function myself()
    {
        //
        $user = \Auth::user();
        return $user;
    }

    public function index(Request $request){

        $uri = $request->path();
        if( in_array('admin',explode('/', $uri))) {
            //------------------------
            // 管理者権限
            //------------------------
            $sortBy = $request->get("sortBy");
            $sortDesc = $request->get("sortDesc");
            if(!$sortBy){
                // 並び順が指定されていない場合、デフォルトは「ユーザ名」項目の昇順で表示する。
                $sortBy = "username";
                $sortDesc = "false";
            }
            $sortStr = "";
            if($sortDesc == "true") {
                $sortStr = "desc";
            }
            elseif($sortDesc == "false") {
                $sortStr = "asc";
            }
            $page = $request->get("page");
            $itemsPerPage = $request->get("itemsPerPage");
            $search = $request->get("search");

            $query = User::select('users.*');
            if( strlen($search) > 0 ) {
                $searchTerm = "%{$search}%";
                $query->whereRaw(
                    "CONCAT(lastname, ',', IFNULL(firstname, ''), ',', IFNULL(email, '') ) LIKE ?", 
                    [$searchTerm]
                );
            }
            if( strlen($sortBy) > 0 ) {
                $query->orderBy($sortBy, $sortStr);
            }
            if($itemsPerPage == '-1'){
                $itemsPerPage = 1000;
            }
            $users = $query->paginate($itemsPerPage, ['*'], 'page', $page);
        }else{
            //------------------------
            // 一般権限
            //------------------------
            $join_model = $request->get("join_model");
            $query = User::select('users.*');

            // 所属部署ユーザー、共有グループユーザー、個別グループユーザー、スケジュール参加者　Join クエリ取得
            $query = $this->getJoinUserQuery($query, $join_model, $request->get("id"));

            // 全ユーザー取得( joinなし)時、ソート順 ID
            // if(strlen($join_model) == 0){
                $query->orderBy('users.username', 'asc');
            // }
            $users = $query->get();
        }
        return $users;
    }

    public function getJoinUserQuery($query, $join_model, $id){

        // 所属部署ユーザー取得
        $query = $this->joinDepartment($query, $join_model, $id);
        // 共有グループユーザー取得
        $query = $this->joinCommonGroup($query, $join_model, $id);
        // 個別グループユーザー取得
        $query = $this->joinIndividualGroup($query, $join_model, $id);
        // スケジュール参加者取得
        $query = $this->joinAttendee($query, $join_model, $id);

        return $query;
    }

    /**
     * @param $query
     * @param $join_model
     * @param $request
     * @return mixed
     */
    private function joinDepartment($query, $join_model, $id)
    {
        if ($join_model == "department") {
            $query->leftJoin(
                "belong_departments", "belong_departments.user_id",
                "=",
                "users.id");
            $query->where('belong_departments.department_id', '=', $id);
            $query->where('users.is_enable', '=', 1);
            $query->where('belong_departments.is_enable', '=', 1);
            $query->where('belong_departments.deleted_at', '=', null);
            $query->orderBy('users.username', 'asc');
            $query->distinct('users.*');
        }
        return $query;
    }

    /**
     * @param $query
     * @param $join_model
     * @param $request
     * @return mixed
     */
    private function joinCommonGroup($query, $join_model, $id)
    {
        if ($join_model == "common_group") {
            // 共有グループユーザー取得
            $query->leftJoin(
                "belong_common_groups", "belong_common_groups.user_id",
                "=",
                "users.id");
            $query->where('belong_common_groups.common_group_id', '=', $id);
            $query->where('users.is_enable', '=', 1);
            $query->where('belong_common_groups.is_enable', '=', 1);
            $query->where('belong_common_groups.deleted_at', '=', null);
            $query->orderBy('users.username', 'asc');
            $query->distinct('users.*');
        }
        return $query;
    }

    /**
     * @param $query
     * @param $join_model
     * @param $request
     * @return mixed
     */
    private function joinIndividualGroup($query, $join_model, $id){

        if ($join_model == "individual_group") {
            // 個別グループユーザー取得
            $query->leftJoin(
                "belong_individual_groups", "belong_individual_groups.user_id",
                "=",
                "users.id");
            $query->where('belong_individual_groups.individual_group_id', '=', $id);
            $query->where('users.is_enable', '=', 1);
            $query->where('belong_individual_groups.is_enable', '=', 1);
            $query->where('belong_individual_groups.deleted_at', '=', null);
            $query->orderBy('users.username', 'asc');
            $query->distinct('users.*');
        }
        return $query;
    }

    /**
     * @param $query
     * @param $join_model
     * @param $request
     * @return mixed
     */
    private function joinAttendee($query, $join_model, $id){

        if ($join_model == "attendee") {
            // スケジュール参加者取得
            $query->leftJoin(
                "attendees", "attendees.user_id",
                "=",
                "users.id");
            $query->where('attendees.schedule_id', '=', $id);
            $query->where('users.is_enable', '=', 1);
            $query->orderBy('users.username', 'asc');
            $query->distinct('users.*');
        }
        return $query;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param UserStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserStoreRequest $request)
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
        $user = User::where('id', $id)->first();

        return $user ?? abort(404);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param UserStoreRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserStoreRequest $request, $id)
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
        // 通知設定削除
        $this->mypage->destroyDefaultReminder($id);
        //ユーザー削除
        User::destroy($id);
        // 部署所属ユーザー削除
        $this->destroyBelongDepartment($id);
        // 共有グループユーザー削除
        $this->destroyBelongCommonGroup($id);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param null $id
     * @return mixed
     */
    private function save(Request $request, $id = null){
        if($id == null){
            // ユーザー登録
            $user = new User();
            $user->fill($request->all())->save();
            // カレンダーリスト登録
            $user = $this->saveCalendarlist($user);
            // 通知設定更新
            $user = $this->mypage->saveDefaultReminder($request, $id, $user);
        }else{
            // ユーザー更新
            $user = User::where('id', $id)->first();
            $user->fill($request->all())->save();
        }
        // 部署所属ユーザー更新
        return $this->saveBelongDepartment($request, $id, $user);
    }

    /**
     * @param Request $request
     * @param null $id
     * @param $user
     * @return mixed
     */
    private function saveBelongDepartment(Request $request, $id = null, $user){
        // 部署所属ユーザー更新
        $requestData = $request->all();
        if($id != null) {
            // 削除
            $submodels = BelongDepartment::where('user_id', $id)->get();
            foreach($submodels as $submodel){
                BelongDepartment::destroy($submodel->id);
            }
        }
        // ユーザー所属部署登録
        foreach($requestData["departments"] as $index => $departmentData) {
            $belong_department = new BelongDepartment();
            $belong_department->user_id = $user->id;
            $belong_department->department_id = $departmentData["value"];
            $belong_department->rank = $index + 1;
            $belong_department->save();
        }

        return $user;
    }

    /**
     * @param $user
     */
    private function saveCalendarlist($user){

        // カレンダーリスト登録
        $calendarlist = new Calendarlist();
        $calendarlist->user_id = $user->id;
        $calendarlist->save();

        $user->calendarlist_id = $calendarlist->id;
        $user->save();
        return $user;
    }

    /**
     * @param $id
     */
    private function destroyBelongDepartment($id){
        // 削除
        $submodels = BelongDepartment::where('user_id', $id)->get();
        foreach($submodels as $submodel){
            BelongDepartment::destroy($submodel->id);
        }
    }

    /**
     * @param $id
     */
    private function destroyBelongCommonGroup($id){
        // 削除
        $submodels = BelongCommonGroup::where('user_id', $id)->get();
        foreach($submodels as $submodel){
            BelongCommonGroup::destroy($submodel->id);
        }
    }

    /**
     * パスワード更新
     *
     * @param PasswordUpdateRequest $request
     * @param $id
     */
    public function password(PasswordUpdateRequest $request, $id)
    {
        //
        $user = User::where('id', $id)->first();
        $password = $request->get("password");
        $user->password = \Hash::make($password);
        $user->save();
    }

    /**
     * ２つのセレクトボックス（TwoSelect）のためのユーザーリスト取得
     *
     * @return array
     */
    public function getUsersForTwoSelect($group_id)
    {
        if( $group_id == 0 ) {
            $users = User::where('is_enable', true)->orderBy('username', 'asc')->get();
        }
        else {
            $users = User::where('is_enable', true)->get();
        }
        $results = [];
        foreach ($users as $user) {
            $results[] = [
                "text" => $user->lastname . " " . $user->firstname,
                "value" => $user->id
            ];
        }
        return $results;
    }

}
