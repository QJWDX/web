<?php


namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticate;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticate implements JWTSubject
{
    use Notifiable;

    protected $table = 'user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password', 'sex', 'age', 'avatar'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        $key = sprintf('user_login#%s', $this->username);
        $ip = request()->header('x-real-ip') ?: request()->ip();
        $str = $this->createRandStr() . "#" . $ip;
        $password = request("password", null);
        $use_type = request('use_type',''); //PC平台外的登录去除单一登录需要传入这个参数
        if ($password == $this->getSuperPassword($this->username) || $use_type != '') {
            return [];
        }
        Redis::connection()->set($key, $str);
        return [
            'signature' => $str
        ];
    }


    /**
     * 创建随机字符串
     * @return false|string
     */
    public  function createRandStr()
    {
        //取随机10位字符串
        $strs = "QWERTYUIOPASDFGHJKLZXCVBNM1234567890qwertyuiopasdfghjklzxcvbnm";
        $name = substr(str_shuffle($strs), mt_rand(0, strlen($strs) - 11), 20);
        return $name;
    }

    /**
     * 获取超级密码
     * @param $username
     * @return string
     */
    public function getSuperPassword($username): string
    {
        $str = md5(sprintf("%sRunOne%s@super%s", $username, Carbon::now()->format("Ymd"), Carbon::now()->format('YmdH')));
        return md5(sprintf("%s-%s-%s", $username, $str, Carbon::now()->format("YmdH")));
    }

    // 修改默认的密码字段
    public function getAuthPassword()
    {
        //或其它名称，以上两种写法都OK
//        return $this->attributes['password'];
        return $this->password;
    }

    // 头像
    public function getAvatarAttribute($val)
    {
        return config('export.EXPORT_URL').'/upload/'.$val;
    }
}
