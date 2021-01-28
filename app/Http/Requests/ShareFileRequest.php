<?php

namespace App\Http\Requests;

use App\Models\Files\ShareFile;
use App\Models\Files\Files;

class ShareFileRequest extends BaseRequest
{
    public function rules()
    {
        switch ($this->method()) {
            case 'GET' :
                return [
                    'resource_type' => ['required', 'string', 'in:file'],
                    'resource_id' => ['required', 'integer',
                        function($attribute, $value, $fail) {
                            switch ($this->input('resource_type')) {
                                case 'file' :
                                    if (! Files::query()->find($value)) {
                                        return $fail('文件不存在!');
                                    }
                                    break;
                                default:
                                    break;
                            }
                        },
                    ],
                ];
                break;
            case 'PUT' :
                return [
                    'resource_type' => ['required', 'string', 'in:file'],
                    'resource_id' => ['required', 'integer',
                        function($attribute, $value, $fail) {
                            $shareFile = ShareFile::query()->where([
                                ['resourceable_type', $this->input('resource_type')],
                                ['resourceable_id', $this->input('resource_id')],
                            ])->first();
                            if (empty($shareFile)) {
                                return $fail('链接刷新出错，该资源还未被分享过');
                            }
                        }]
                ];
                break;
            default:
                return [];
                break;
        }

    }


    public function messages()
    {
        return [
            'resource_type.required' => '分享的资源类型不能为空',
            'resource_type.string' => '分享的资源类型必须为字符串',
            'resource_type.in' => '分享的资源类型不正确',
            'resource_id.required' => '资源id不能为空',
            'resource_id.integer' => '资源id必须为整数',
            'share_link.required' => '分享码不能为空',
            'share_link.string' => '分享码必须为字符串',
        ];
    }
}
