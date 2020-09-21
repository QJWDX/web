<?php


namespace App\Http\Requests;

class DelRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'required'
        ];
    }

    public function messages()
    {
        return [
            'ids.required' => '至少选择一项',
            'ids.array' => '选择项必须为数组',
            'ids.min' => '选择项至少要含有一项元素',
            'ids.*.required' => '选择项必填',
        ];
    }
}
