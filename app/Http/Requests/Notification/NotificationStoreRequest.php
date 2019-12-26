<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class NotificationStoreRequest extends FormRequest
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
        }
        /*
         * 新規登録画面の時
         */
        else {
        }

        return [
            'schedule_id' => 'required',
            'user_id' => 'required',
        ];
    }
}
