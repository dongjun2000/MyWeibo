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
    public function __construct()
    {
        // 限流：发送密码重置邮件，10分钟内只能尝试3次
        $this->middleware('throttle:3,10', [
            'only' => ['sendResetLinkEmail']
        ]);
    }

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
        return view('auth.passwords.reset', compact('token'));
    }

    // POST /password/reset 重置用户密码操作
    public function reset(Request $request)
    {
        // 1.表单验证
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);
        $email = $request->email;
        $token = $request->token;

        $expires = 60 * 10;     // 秒，找回密码链接的有效时间

        // 2.获取对应用户
        $user = User::where('email', $email)->first();

        // 3.如果用户不存在
        if (is_null($user)) {
            session()->flash('danger', '邮箱未注册');
            return redirect()->back()->withInput();
        }

        // 4.读取重置的记录
        $record = (array)DB::table('password_resets')->where('email', $email)->first();

        // 5.记录存在
        if ($record) {
            // 5.1 检查是否过期
            if (Carbon::parse($record['created_at'])->addSeconds($expires)->isPast()) {
                return redirect()->back()->with('danger', '链接已过期，请重新尝试');
            }

            // 5.2 检查是否正确
            if (Hash::check($token, $record['token'])) {
                return redirect()->back()->with('danger', '令牌错误');
            }

            // 5.3 一切正常，更新用户密码
            $user->update(['password' => bcrypt($request->password)]);

            return redirect()->route('login')->with('success', '密码重置成功，请使用新密码登录');
        }

        // 6.记录不存在
        return redirect()->back()->with('danger', '请先提交申请密码重置请求');
    }
}
