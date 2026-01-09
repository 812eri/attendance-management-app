<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    //管理者ログインの表示
    public function login()
    {
        return view('admin.auth.login');
    }

    public function loginstore(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required' ,'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            if (Auth::user()->isAdmin()) {
                return redirect()->route('admin.attendance.list');
            }
            Auth::logout();
            return back()->withErrors([
                'email' => '管理者権限がありません。',
            ]);
        }

        return back()->witherrors([
            'email' => 'ログイン情報が登録されていません。',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
