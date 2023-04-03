<?php

namespace App\Http\Controllers;

use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatusesController extends Controller
{
    public function __construct()
    {
        // 需要用户登录才能访问
        $this->middleware('auth');
    }

    // POST /statuses 发微博操作
    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|max:140'
        ]);

        Auth::user()->statuses()->create([
            'content' => $request->content
        ]);

        return redirect()->back()->with('success', '发布成功！');
    }

    public function destroy(Status $status)
    {
        $this->authorize('destroy', $status);
        $status->delete();
        return redirect()->back()->with('success', '微博已被成功删除！');
    }
}
