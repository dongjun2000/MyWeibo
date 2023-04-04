<?php

namespace App\Models;

use App\Models\Status;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // boot方法会在用户模型类完成初始化之后进行加载
    public static function boot()
    {
        parent::boot();

        // 用于监听模型被创建之前的事件
        static::creating(function ($user) {
            $user->activation_token = Str::random(10);
        });
    }

    // 模型关联关系：一个用户拥有多条微博
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    // 模型关联关系：获取我的所有粉丝
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }

    // 模型关联关系：获取我关注的所有人
    public function followings()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    // 获取用户头像
    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "https://cdn.v2ex.com/gravatar/$hash?s=$size";
    }

    // 获取微博信息流
    public function feed()
    {
        return $this->statuses()->orderBy('created_at', 'desc');
    }

    // 关注
    public function follow($user_ids)
    {
        if (is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids, false);
    }

    // 取消关注
    public function unfollow($user_ids)
    {
        if (is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }

    // 判断当前用户是否关注了某个指定用户
    public function isFollowing($user_id)
    {
        return $this->followings()->contains($user_id);
    }
}
