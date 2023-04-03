<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['create', 'store', 'show', 'index', 'confirmEmail']
        ]);

        // 只有未登录用户才能访问注册页面
        $this->middleware('guest', [
            'only' => ['create']
        ]);

        // 限流：一个小时内只能提交10次请求
        $this->middleware('throttle:10,60', [
            'only' => ['store']
        ]);
    }

    // 用户列表
    public function index()
    {
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    // GET /signup 注册用户
    public function create()
    {
        return view('users.create');
    }

    // 个人中心
    public function show(User $user)
    {
        $statuses = $user->statuses()->orderBy('created_at', 'desc')->paginate(30);
        return view('users.show', compact('user', 'statuses'));
    }

    // POST /users 注册用户操作
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:users|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6',
        ]);

        // 创建用户
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // 注册成功之后，登录当前用户
        // Auth::login($user);

        // 发送激活账户邮件
        $this->sendEmailConfirmationTo($user);

        return redirect()
            ->route('home')
            ->with('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
    }

    public function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $to = $user->email;
        $subject = '感谢注册 Weibo 应用！请确认你的邮箱。';

        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6',
        ]);

        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = $request->password;
        }
        $user->update($data);

        return redirect()->route('users.show', $user)->with('success', '个人资料更新成功！');
    }

    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        return back()->with('success', '成功删除用户');
    }

    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        return redirect()->route('users.show', [$user])->with('success', '恭喜你，激活成功！');
    }
}
