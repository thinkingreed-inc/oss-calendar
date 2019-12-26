<?php

namespace App\Http\Controllers;

use App\Models\DefaultReminder;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

use App\Http\Requests\EnableDisableUpdateRequest;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 有効無効更新
     *
     * @param EnableDisableUpdateRequest $request
     * @param $id
     */
    public function enableDisable(EnableDisableUpdateRequest $request, $modelName, $id)
    {
        $modelPath = '\App\Models\\'.$modelName;
        if(!class_exists($modelPath)) {
            Log::debug($modelPath + " is not found");
        }

        // 有効・無効 更新
        $model =  $modelPath::where('id', $id)->first();
        $change_is_enable = null;
        if ($model->is_enable) {
            $change_is_enable = 0;
        }
        else {
            $change_is_enable = 1;
        }
        $model->is_enable = $change_is_enable;
        $model->save();

        // デフォルトリマインダーの有効・無効
        if($modelName == "User") {
            $this->enableDisableDefaultReminder($id, $change_is_enable);
        }

        // 部署所属ユーザーの有効・無効
        $this->enableDisableBelongDepartment($request, $modelName, $id, $change_is_enable);

        // 共有グループユーザーの有効・無効
        $this->enableDisableBelongCommonGroup($request, $modelName, $id, $change_is_enable);

        // 個別グループユーザーの有効・無効
        $this->enableDisableBelongIndividualGroup($request, $modelName, $id, $change_is_enable);

        return $model;
    }

    /**
     * @param $request
     * @param $modelName
     * @param $id
     * @param $change_is_enable
     */
    private function enableDisableDefaultReminder($id, $change_is_enable){

        $subModels = DefaultReminder::where('calendarlist_id', $id)->get();
        // 有効・無効 更新
        if(!empty($subModels)) {
            foreach ($subModels as $subModel) {
                $subModel->is_enable = $change_is_enable;
                $subModel->save();
            }
        }
    }

    /**
     * @param $request
     * @param $modelName
     * @param $id
     * @param $change_is_enable
     */
    private function enableDisableBelongDepartment($request, $modelName, $id, $change_is_enable){
        // 部署所属ユーザーの有効・無効
        $modelPath = '\App\Models\BelongDepartment';
        if (!class_exists($modelPath)) {
            Log::debug($modelPath + " is not found");
        }
        // モデルによって条件変更
        if($modelName == "User") {
            $subModels = $modelPath::where('user_id', $id)->get();
        }
        if($modelName == "Department") {
            $subModels = $modelPath::where('department_id', $id)->get();
        }
        // 有効・無効 更新
        if(!empty($subModels)) {
            foreach ($subModels as $subModel) {
                $subModel->is_enable = $change_is_enable;
                $subModel->save();
            }
        }
    }

    /**
     * @param $request
     * @param $modelName
     * @param $id
     * @param $change_is_enable
     */
    private function enableDisableBelongCommonGroup($request, $modelName, $id, $change_is_enable){
        // 共有グループユーザーの有効・無効
        $modelPath = '\App\Models\BelongCommonGroup';
        if(!class_exists($modelPath)) {
            Log::debug($modelPath + " is not found");
        }
        // モデルによって条件変更
        if($modelName == "User") {
            $subModels = $modelPath::where('user_id', $id)->get();
        }
        if($modelName == "CommonGroup") {
            $subModels = $modelPath::where('common_group_id', $id)->get();
        }
        // 有効・無効 更新
        if(!empty($subModels)) {
            foreach ($subModels as $subModel) {
                $subModel->is_enable = $change_is_enable;
                $subModel->save();
            }
        }

    }

    /**
     * @param $request
     * @param $modelName
     * @param $id
     * @param $change_is_enable
     */
    private function enableDisableBelongIndividualGroup($request, $modelName, $id, $change_is_enable){
        // 個別グループユーザーの有効・無効
        $modelPath = '\App\Models\BelongIndividualGroup';
        if(!class_exists($modelPath)) {
            Log::debug($modelPath + " is not found");
        }
        // モデルによって条件変更
        if($modelName == "User") {
            $subModels = $modelPath::where('user_id', $id)->get();
        }
        if($modelName == "IndividualGroup") {
            $subModels = $modelPath::where('Individual_group_id', $id)->get();
        }
        // 有効・無効 更新
        if(!empty($subModels)) {
            foreach ($subModels as $subModel) {
                $subModel->is_enable = $change_is_enable;
                $subModel->save();
            }
        }

    }
}
