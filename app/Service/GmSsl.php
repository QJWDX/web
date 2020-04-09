<?php


namespace App\Service;

use App\Exceptions\InvalidPathException;
use Illuminate\Support\Facades\Storage;

class GmSsl
{
    public static $private_key;
    public static $public_key;
    public static $sign;
    public static $cipher_text;
    public static $plain_text;
    public static $user;

    const PEM_SAVE_PATH = "app/";
    const PRIVATE_PEM_PREFIX = "sm2_pem_";
    const PUBLIC_PEM_PREFIX = "sm2_public_pem_";
    const SIGN_PEM_PREFIX = "/sm2_sign_";
    const PEM_EXT = ".pem";
    const SIGN_EXT = '.sig';

    /**
     * 创建密钥
     * @param $user
     * @return string
     */
    public static function createPrivate($user)
    {
        //使用openssl创建私钥
        $private_file_name = storage_path(self::PEM_SAVE_PATH . self::PRIVATE_PEM_PREFIX . $user . self::PEM_EXT);

        exec("gmssl ecparam -genkey -name SM2 -out " . $private_file_name);

        self::$private_key = file_get_contents($private_file_name);

        return $private_file_name;
    }

    /**
     * 创建公钥
     * @param $private_key
     * @param $user
     * @return string
     */
    public static function createPublicByPrivate($user, $private_key)
    {
        //使用openssl创建公钥
        $public_file_name = storage_path(self::PEM_SAVE_PATH . self::PUBLIC_PEM_PREFIX . $user . self::PEM_EXT);

        exec(sprintf('gmssl ec -in %s -pubout -out %s', $private_key, $public_file_name), $out, $val);

        self::$public_key = file_get_contents($public_file_name);

        return $public_file_name;
    }

    /**
     * 解密
     * @param $private_key
     * @param $user
     * @param $cipher_text
     * @return string
     */
    public static function decodeSm2($user, $private_key, $cipher_text)
    {
        //验证签名
        $result = self::createTempFile(self::createFileName($user));
        //创建temp文件
        $cipher_text = self::createTempFile(self::createFileName($user), $cipher_text);
        $private_key = self::createTempFile(self::createFileName($user), $private_key);

        //gmssl  sm2utl  -decrypt  -inkey  "priv.key"  -in  ciphertext.sm2  -out  result.txt
        $cmd = sprintf("gmssl  sm2utl  -decrypt  -inkey  %s  -in  %s  -out  %s", $private_key, $cipher_text, $result);

        exec($cmd);

        self::deleteTempFile([$cipher_text, $private_key]);
        return $result;
    }

    /**
     * @param $plain_text
     * @param $public_Key
     * @param $user
     * @return string
     */
    public static function encodeSm2($user, $plain_text, $public_Key)
    {
        $out_file = self::createTempFile(self::createFileName($user));
        $plain_text = self::createTempFile(self::createFileName($user), $plain_text);
        $public_Key = self::createTempFile(self::createFileName($user), $public_Key);
        $cmd = sprintf("gmssl  sm2utl  -encrypt  -in  %s  -pubin  -inkey  %s  -out  %s", $plain_text, $public_Key, $out_file);
        exec($cmd);

        //删除无用文件
        self::deleteTempFile([$plain_text, $public_Key]);

        return $out_file;
    }

    /**
     * 签名
     * @param $user
     * @param $plain_text
     * @param $private_key
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public static function signText($user, $plain_text, $private_key)
    {
        //生成签名文件
        $storage_path = self::SIGN_PEM_PREFIX . $user . self::SIGN_EXT;

        $plain_text = self::createTempFile(self::createFileName($user), $plain_text);
        $private_key = self::createTempFile(self::createFileName($user), $private_key);

        Storage::disk("local")->put($storage_path, '');

        $out_sign_file = storage_path(self::PEM_SAVE_PATH . $storage_path);

        $cmd = sprintf("gmssl  sm2utl  -sign  -in  %s  -inkey  %s  -out  %s  -id  %s", $plain_text, $private_key, $out_sign_file, $user);

        exec($cmd, $out, $var);

        self::$sign = Storage::disk("local")->get($storage_path);

        self::deleteTempFile([$plain_text, $private_key]);
        return $out_sign_file;
    }

    /**
     * 验证签名
     * @param $plain_txt
     * @param $public_key
     * @param $sign
     * @param $user
     * @return bool
     */
    public static function verifySign($user, $sign, $plain_txt, $public_key, $default_id = false)
    {
        //创建临时文件
        $plain_txt = self::createTempFile(self::createFileName($user), $plain_txt);
        $public_key = self::createTempFile(self::createFileName($user), $public_key);
        $sign = self::createTempFile(self::createFileName($user), $sign);

        if ($default_id) {
            $user = '1234567812345678';
        }
        $cmd = sprintf("gmssl  sm2utl  -verify  -in  %s  -sigfile  %s  -inkey  %s  -pubin  -id  %s", $plain_txt, $sign, $public_key, $user);

        exec($cmd, $out, $val);

        self::deleteTempFile([$plain_txt, $public_key, $sign]);

        return self::checkVerifyResult($out);
    }

    /**
     * 检验结果
     * @param $result
     * @return bool
     */
    protected static function checkVerifyResult($result)
    {
        $line = implode("", $result);
        if (strpos($line, "Successful") !== false) {
            return true;
        }
        return false;
    }

    /**
     * 创建临时文件
     * @param $file_name
     * @param $content
     * @return string
     */
    protected static function createTempFile($file_name, $content = '')
    {
        //user,+ ip + port md5
//        Storage::disk("pem")->put($file_name, $content);
        $real_file_name = config("filesystems.disks.pem.root") . $file_name;
        @mkdir(dirname($real_file_name), 0777, true);
        file_put_contents($real_file_name, $content);

        return $real_file_name;
    }

    /**
     * 创建文件名
     * @param $user
     * @return string
     */
    protected static function createFileName($user)
    {
        $filename = md5(self::uuid() . $user . rand(1, 99999));

        return $filename;
    }

    /**
     * 创建uuid
     * @return string
     */
    protected static function uuid()
    {
        mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = substr($charid, 0, 8) . $hyphen
            . substr($charid, 8, 4) . $hyphen
            . substr($charid, 12, 4) . $hyphen
            . substr($charid, 16, 4) . $hyphen
            . substr($charid, 20, 12);
        return $uuid;
    }

    protected static function deleteTempFile($paths)
    {
        if (is_array($paths)) {
            foreach ($paths as $path) {
                @unlink($path);
            }
        } else {
            @unlink($paths);
        }
    }

}
