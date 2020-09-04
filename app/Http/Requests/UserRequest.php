<?php


namespace App\Http\Requests;


use Illuminate\Validation\Rule;

class UserRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|min:2|max:6',
            'username' => 'required|string|min:2|max:16',
            'tel' => 'required|regex:/^1[3456789]\d{9}$/|unique:user,tel',
            'email' => 'required|email|unique:user,email',
            'sex' => 'required|integer|'.Rule::in([-1, 0, 1]),
            'status' => 'required|integer|'.Rule::in([0, 1]),
        ];
    }

    public function messages()
    {
        return [
            'name.required' => '姓名必填',
            'name.min' => '姓名长度不小于两位',
            'name.max' => '姓名长度不大于六位',
            'username.required' => '姓名必填',
            'username.min' => '姓名长度不小于2个字符',
            'username.max' => '姓名长度不大于16个字符',
            'email.required' => '邮箱必填',
            'email.email' => '邮箱格式错误',
            'email.unique' => '邮箱已存在，请换一个',
            'phone.required' => '电话必填',
            'phone.regex' => '电话格式错误',
            'phone.unique' => '电话已存在，请换一个',
            'sex.required' => '性别必填',
            'sex.in' => '性别无效',
            'status.required' => '状态必填',
            'status.in' => '状态无效',
        ];
    }
}
