<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{

    public function __construct()
    {
        // 未登录用户才能访问 登录页面
        $this->middleware('guest', [
            'only' => ['create']
        ]);

        // 限流 10 分钟内只能尝试登录10次
        $this->middleware('throttle:10,10', [
            'only' => ['store']
        ]);
    }

    // GET /login 用户登录页面
    public function create()
    {
        return view('sessions.create');
    }

    // POST /login 用户登录操作
    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->has('remember'))) {
            // 判断用户是否已激活
            if (Auth::user()->activated) {
                session()->flash('success', '欢迎回来！');
                $fallback = route('users.show', Auth::user());
                return redirect()->intended($fallback);
            } else {
                // 未激活，退出登录
                Auth::logout();
                session()->flash('warning', '你的账号未激活，请检查邮箱中的注册邮件进行激活。');
                return redirect('/');
            }
        } else {
            session()->flash('danger', '很抱歉，您的邮箱或密码不正确');
            return redirect()->back()->withInput();
        }
    }

    public function destroy()
    {
        Auth::logout();
        return redirect('login')->with('success', '您已经成功退出！');
    }
}
