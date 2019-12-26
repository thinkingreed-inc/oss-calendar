<?php

namespace App\Http\Controllers;

use App\Models\IndividualGroup;
use App\Models\BelongIndividualGroup;
use Illuminate\Http\Request;
use App\Http\Requests\IndividualGroup\IndividualGroupStoreRequest;

class IndividualGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $my = \Auth::user();
        $join_model = $request->get("join_model");

        if($join_model == "user") {
            // ユーザー個別グループ取得
            $query = IndividualGroup::select('individual_groups.*');
            $query->leftJoin(
                "belong_individual_groups", "belong_individual_groups.individual_group_id",
                "=",
                "individual_groups.id");
            $query->where('belong_individual_groups.user_id', '=', $request->get("user_id"));
            $query->where('individual_groups.is_enable', '=', 1);
            $query->where('belong_individual_groups.is_enable', '=', 1);
            $query->where('belong_individual_groups.deleted_at', '=', null);
            $query->orderBy('individual_groups.id', 'asc');
            $query->distinct('individual_groups.*');
            $individual_groups = $query->get();
        }else{
            // 全ユーザー取得
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

            $query = IndividualGroup::select('*');
            if (strlen($search) > 0) {
                $query->where("name", 'LIKE', "%{$search}%");
            }
            if (strlen($sortBy) > 0) {
                $query->orderBy($sortBy, $sortStr);
            }
            $individual_groups = $query->paginate($itemsPerPage, ['*'], 'page', $page);
        }
        return $individual_groups;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param IndividualGroupStoreRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(IndividualGroupStoreRequest $request)
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
        $individual_group = IndividualGroup::where('id', $id)->first();
        return $individual_group ?? abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param IndividualGroupStoreRequest $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(IndividualGroupStoreRequest $request, $id)
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
        // 個別グループ削除
        IndividualGroup::destroy($id);
        // 個別グループユーザー削除
        $this->destroyBelongIndividualGroup($id);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param null $id
     * @return mixed
     */
    private function save(Request $request, $id = null)
    {
        if ($id == null) {
            // 個別グループ登録
            $individual_group = new IndividualGroup();
            $individual_group->fill($request->all())->save();
        } else {
            // 個別グループ更新
            $individual_group = IndividualGroup::where('id', $id)->first();
            $individual_group->fill($request->all())->save();
        }

        // 個別グループユーザー更新
        return $this->saveBelongIndividualGroup($request, $id, $individual_group);
    }

    /**
     * @param Request $request
     * @param null $id
     * @param $individual_group
     * @return mixed
     */
    private function saveBelongIndividualGroup(Request $request, $id = null, $individual_group)
    {
        // 個別グループユーザー更新
        $requestData = $request->all();
        if($id != null) {
            // 削除
            $submodels = BelongIndividualGroup::where('individual_group_id', $id)->get();
            foreach ($submodels as $submodel) {
                BelongIndividualGroup::destroy($submodel->id);
            }
        }
        // 登録
        foreach ($requestData["users"] as $index => $userData) {
            $belong_individual_group = new BelongIndividualGroup();
            $belong_individual_group->user_id = $userData["value"];
            $belong_individual_group->individual_group_id = $individual_group->id;
            $belong_individual_group->rank = $index + 1;
            $belong_individual_group->save();
        }

        return $individual_group;
    }

    /**
     * @param $id
     */
    private function destroyBelongIndividualGroup($id){
        // 削除
        $submodels = BelongIndividualGroup::where('individual_group_id', $id)->get();
        foreach ($submodels as $submodel) {
            BelongIndividualGroup::destroy($submodel->id);
        }
    }
}
