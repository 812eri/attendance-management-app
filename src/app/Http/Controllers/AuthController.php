<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    public function register(RegisterRequest $request, CreateNewUser $creator)
    {
        $user = $creator->create($request->all());

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('attendance.index');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended(route('attendance.index'));
        }

        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->onlyInput('email');
    }
}
