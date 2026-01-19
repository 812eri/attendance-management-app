@extends('layouts.guest')

@section('title', 'ログイン')

@section('content')
<div class="auth-wrapper">
    <h1 class="auth-header">ログイン</h1>

    <form action="/login" method="post">
        @csrf

        <div class="form-group">
            <label class="form-label" for="email">メールアドレス</label>
            <input class="form-input" type="email" name="email" id="email" value="{{ old('email') }}">
            @error('email')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label class="form-label" for="password">パスワード</label>
            <input class="form-input" type="password" name="password" id="password">
            @error('password')
            <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <button class="form-btn" type="submit">ログインする</button>
    </form>

    <a class="auth-link" href="/register">会員登録はこちら</a>
</div>
@endsection