<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', [
            'except' => ['create', 'store', 'show', 'index']
        ]);

        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    // 用户列表
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    // 注册用户
    public function create()
    {
        return view('users.create');
    }

    // 个人中心
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    // 注册用户操作
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:users|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // 注册成功之后，登录当前用户
        Auth::login($user);

        return redirect()
            ->route('users.show', ['user' => $user])
            ->with('success', '欢迎，您将在这里开启一段新的旅程~');
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
}
