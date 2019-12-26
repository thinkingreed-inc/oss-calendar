<?php

namespace App\Http\Requests\Mypage;

use Illuminate\Foundation\Http\FormRequest;

class MySettingStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        /*
         * 編集画面の時
         */
        if ($this -> id) {
            $reminderRule = 'required_if:default_reminders_method_id, 1';
        }
        /*
         * 新規登録画面の時
         */
        else {
            $reminderRule = 'required_if:default_reminders_method_id, 1';
        }

        return [
            'overrides_minutes' => $reminderRule,
        ];
    }
}
