<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;


class PasswordController extends Controller
{
    // GET /password/reset 找回密码页面
    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    // POST /password/email 发送重置密码链接
    public function sendResetLinkEmail(Request $request)
    {
        // 1.验证邮箱
        $this->validate($request, [
            'email' => 'required|email',
        ]);
        $email = $request->email;

        // 2.获取对应用户
        $user = User::where("email", $email)->first();

        // 3.如果用户不存在
        if (is_null($user)) {
            session()->flash('danger', '邮箱未注册');
            return redirect()->back()->withInput();
        }

        // 4.生成 Token，会在视图 email.reset_link 里拼接链接
        $token = hash_hmac('sha256', Str::random(40), config('app.key'));

        // 5.入库，使用 updateOrInsert 来保持 Email 唯一
        DB::table('password_resets')->updateOrInsert(['email' => $email], [
            'email' => $email,
            'token' => Hash::make($token),
            'created_at' => new Carbon,
        ]);

        /**
         * 6.将 Token 链接发送给用户
         *
         * 第一个参数：包含邮件消息的视图名称。
         * 第二个参数：传递给该视图的数据数组。
         * 第三个参数：用来接收邮件消息实例的闭包回调。
         */
        Mail::send('emails.reset_link', compact('token'), function ($message) use ($email) {
            $message->to($email)->subject('忘记密码');
        });

        session()->flash('success', '重置邮件发送成功，请查收');
        return redirect()->back();
    }

    // GET /password/reset/{token} 显示更新密码的表单，包含token
    public function showResetForm($token)
    {

    }
}
