<?php

namespace App\Http\Controllers;

use App\Models\CommonGroup;
use App\Models\BelongCommonGroup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\CommonGroup\CommonGroupStoreRequest;

class CommonGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $uri = $request->path();
        if( in_array('admin',explode('/', $uri))) {
            //------------------------
            // 管理者権限
            //------------------------
            $sortBy = $request->get("sortBy");
            $sortDesc = $request->get("sortDesc");
            $sortStr = "";
            if ($sortDesc == "true") {
                $sortStr = "desc";
            } elseif ($sortDesc == "false") {
                $sortStr = "asc";
            }
            $page = $request->get("page");
            $itemsPerPage = $request->get("itemsPerPage");
            $search = $request->get("search");

            $query = CommonGroup::select('*');
            if (strlen($search) > 0) {
                $query->where("name", 'LIKE', "%{$search}%");
            }
            if (strlen($sortBy) > 0) {
                $query->orderBy($sortBy, $sortStr);
            }
            if($itemsPerPage == '-1'){
                $itemsPerPage = 1000;
            }
            $common_groups = $query->paginate($itemsPerPage, ['*'], 'page', $page);
        }else{
            //------------------------
            // 一般権限
            //------------------------
            $join_model = $request->get("join_model");
            $query = CommonGroup::select('common_groups.*');
            if($join_model == "user") {
                // ユーザー共有グループ取得
                $query->leftJoin(
                    "belong_common_groups", "belong_common_groups.common_group_id",
                    "=",
                    "common_groups.id");
                $query->where('common_groups.is_enable', '=', 1);
                $query->where('belong_common_groups.is_enable', '=', 1);
                $query->where('belong_common_groups.deleted_at', '=', null);
                $query->orderBy('common_groups.id', 'asc');
                $query->distinct('common_groups.*');
            }else{
                // 全部署取得
                $query->orderBy('common_groups.id', 'asc');
            }
            $common_groups = $query->get();
        }
        return $common_groups;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param CommonGroupStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(CommonGroupStoreRequest $request)
    {
        return $this->save($request);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $common_group = CommonGroup::where('id', $id)->first();
        return $common_group ?? abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param CommonGroupStoreRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(CommonGroupStoreRequest $request, $id)
    {
        return $this->save($request, $id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // 共有グループ削除
        CommonGroup::destroy($id);
        // 共有グループユーザー削除
        $this->destroyBelongCommonGroup($id);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param null $id
     * @return mixed
     */
    private function save(Request $request, $id = null)
    {
        if ($id == null) {
            // 共有グループ登録
            $common_group = new CommonGroup();
            $common_group->fill($request->all())->save();
        } else {
            // 共有グループ更新
            $common_group = CommonGroup::where('id', $id)->first();
            $common_group->fill($request->all())->save();
        }

        // 共有グループユーザー更新
        return $this->saveBelongCommonGroup($request, $id, $common_group);
    }

    /**
     * @param Request $request
     * @param null $id
     * @param $common_group
     * @return mixed
     */
    private function saveBelongCommonGroup(Request $request, $id = null, $common_group)
    {
        // 共有グループユーザー更新
        $requestData = $request->all();
        if($id != null) {
            // 削除
            $submodels = BelongCommonGroup::where('common_group_id', $id)->get();
            foreach ($submodels as $submodel) {
                BelongCommonGroup::destroy($submodel->id);
            }
        }
        // 登録
        foreach ($requestData["users"] as $index => $userData) {
            $belong_common_group = new BelongCommonGroup();
            $belong_common_group->user_id = $userData["value"];
            $belong_common_group->common_group_id = $common_group->id;
            $belong_common_group->rank = $index + 1;
            $belong_common_group->save();
        }

        return $common_group;
    }

    /**
     * @param $id
     */
    private function destroyBelongCommonGroup($id){
        // 削除
        $submodels = BelongCommonGroup::where('common_group_id', $id)->get();
        foreach ($submodels as $submodel) {
            BelongCommonGroup::destroy($submodel->id);
        }
    }
}
