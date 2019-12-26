<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
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
            $usernameRule = 'required|unique:users,username,' . $this -> id . ',id';
            $emailRule = 'email|unique:users,email,' . $this -> id . ',id';
            $reminderRule = 'required_if:default_reminders_method_id, 1';
        }
        /*
         * 新規登録画面の時
         */
        else {
            $usernameRule = 'required|unique:users,username';
            $emailRule = 'email|unique:users,email';
            $reminderRule = 'required_if:default_reminders_method_id, 1';
        }

        return [
            'username' => $usernameRule,
            'email' => $emailRule,
            'lastname' => 'required',
            'firstname' => 'required',
            'overrides_minutes' => $reminderRule,
        ];
    }
}
