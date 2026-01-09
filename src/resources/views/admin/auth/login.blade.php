@extends('layouts.guest')

@section('title', '管理者ログイン')

@section('content')
<div class="auth-wrapper">
    <h1 class="auth-header">管理者ログイン</h1>

    <form action="{{ route('admin.login') }}" method="post">
        @csrf

        <div class="form-group">
            <label class="form-label" for="email">メールアドレス</label>
            <input class="form-input" type="email" name="email" id="email" value="{{ old('email') }}">
            @error('email')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password">パスワード</label>
            <input class="form-input" type="password" name="password" id="password">
            @error('password')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        <button class="form-btn" type="submit">管理者ログインする</button>
    </form>
</div>
@endsection