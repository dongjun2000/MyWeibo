<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $fillable = ['content'];

    // 模型关联关系：一条微博属于一个用户
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
