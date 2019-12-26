<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\BelongDepartment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Department\DepartmentStoreRequest;

class DepartmentController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $uri = $request->path();
        if( in_array('admin',explode('/', $uri))){
            //------------------------
            // 管理者権限
            //------------------------
            $sortBy = $request->get("sortBy");
            $sortDesc = $request->get("sortDesc");
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
            $query = Department::select('*');
            if( strlen($search) > 0 ) {
                $query->where('name', 'LIKE', "%{$search}%");
            }
            if( strlen($sortBy) > 0 ) {
                $query->orderBy($sortBy, $sortStr);
            }
            $departments = $query->paginate($itemsPerPage, ['*'], 'page', $page);
        }else{
            //------------------------
            // 一般権限
            //------------------------
            $join_model = $request->get("join_model");
            $query = Department::select('departments.*');

            if($join_model == "user") {
                // ユーザー所属部署取得
                $query->leftJoin(
                    "belong_departments", "belong_departments.department_id",
                    "=",
                    "departments.id");
                $query->where('belong_departments.user_id', '=', $request->get("user_id"));
                $query->where('departments.is_enable', '=', 1);
                $query->where('belong_departments.is_enable', '=', 1);
                $query->where('belong_departments.deleted_at', '=', null);
                $query->orderBy('departments.id', 'asc');
                $query->distinct('departments.*');
            }else{
                // 全部署取得
                $query->orderBy('departments.id', 'asc');
            }
            $departments = $query->get();
        }
        return $departments;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param DepartmentStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(DepartmentStoreRequest $request)
    {
        return $this->save($request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param DepartmentStoreRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(DepartmentStoreRequest $request, $id)
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
        // 部署削除
        Department::destroy($id);
        // 部署所属ユーザー削除
        $this->destroyBelongDepartment($id);
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
        $department = Department::where('id', $id)->first();
        return $department ?? abort(404);

    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param null $id
     * @return mixed
     */
    private function save(Request $request, $id = null)
    {
        if ($id == null) {
            // 部署登録
            $department = new Department();
            $department->fill($request->all())->save();
        } else {
            // 部署更新
            $department = Department::where('id', $id)->first();
            $department->fill($request->all())->save();
        }

        // 部署所属ユーザー更新
        return $this->saveBelongDepartment($request, $id, $department);
    }

    private function saveBelongDepartment(Request $request, $id = null, $department){
        // 部署所属ユーザー更新
        $requestData = $request->all();
        if($id != null) {
            // 削除
            $submodels = BelongDepartment::where('department_id', $id)->get();
            foreach($submodels as $submodel){
                BelongDepartment::destroy($submodel->id);
            }
        }
        // 登録
        foreach($requestData["users"] as $index => $userData) {
            $belong_department = new BelongDepartment();
            $belong_department->user_id = $userData["value"];
            $belong_department->department_id = $department->id;
            $belong_department->rank = $index + 1;
            $belong_department->save();
        }

        return $department;
    }

    /**
     * @param $id
     */
    private function destroyBelongDepartment($id){
        // 削除
        $submodels = BelongDepartment::where('department_id', $id)->get();
        foreach($submodels as $submodel){
            BelongDepartment::destroy($submodel->id);
        }
    }

    /**
     * ２つのセレクトボックス（TwoSelect）のためのユーザーリスト取得
     *
     * @return array
     */
    public function getDepartmentsForTwoSelect()
    {
        $departments = Department::where('is_enable', true)->get();
        $results = [];
        foreach ($departments as $department) {
            $results[] = [
                "text" => $department->name,
                "value" => $department->id
            ];
        }
        return $results;
    }

}
