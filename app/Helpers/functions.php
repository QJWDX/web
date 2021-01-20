<?php

namespace App\Helpers {

    /**
     * curl请求
     * @param $url
     * @param string $method
     * @param array $header
     * @param array $content
     * @return array|bool|false|string
     */
    function curl_request($url, $method = 'GET', $header = [], $content = [])
    {
        $method = strtoupper($method);
        $ch = curl_init();
        if (substr($url, 0, 5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 跳过证书检查
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);  // 从证书中检查SSL加密算法是否存在
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $header = $header ?? ["Content-Type: application/json; charset=utf-8"];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
        }
        if (!empty($content) && $method == 'POST') {
            $content_json = json_encode($content);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $content_json);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        $response = curl_exec($ch);
        $res_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($res_code == 403) {
            $response = ["code" => 403];
            $response = json_encode($response);
        }
        if ($error = curl_error($ch)) {
            curl_close($ch);
            return $error;
        }
        curl_close($ch);
        return $response;
    }

    /**
     * 数组依据字段分组
     * @param $array
     * @param $key
     * @return array
     */
    function array_group($array, $key)
    {
        $result = [];
        foreach ($array as $k => $v) {
            $result[$v[$key]][] = $v;
        }
        return $result;
    }

    /**
     * 创建uuid
     * @return string
     */
    function uuid()
    {
        // optional for php 4.2.0 and up.
        mt_srand((double)microtime() * 10000);
        $char_id = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);
        return substr($char_id, 0, 8) . $hyphen
            . substr($char_id, 8, 4) . $hyphen
            . substr($char_id, 12, 4) . $hyphen
            . substr($char_id, 16, 4) . $hyphen
            . substr($char_id, 20, 12);
    }

    /**
     * 参数检测
     * @param array $params
     * @param string $key
     * @return bool
     */
    function checkParam(array $params, string $key){
        if(!$params) return false;
        return isset($params[$key]) && $params[$key];
    }
}
