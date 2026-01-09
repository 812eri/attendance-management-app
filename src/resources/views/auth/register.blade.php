@extends('layouts.guest')

@section('title', '会員登録')

@section('content')
<div class="auth-wrapper">
    <h1 class="auth-header">会員登録</h1>

    <form action="/register" method="post">
        @csrf

        <div class="form-group">
            <label class="form-label" for="name">名前</label>
            <input class="form-input" type="text" name="name" id="name">
        </div>

        <div class="form-group">
            <label class="form-label" for="email">メールアドレス</label>
            <input class="form-input" type="email" name="email" id="email">
        </div>

        <div class="form-group">
            <label class="form-label" for="password">パスワード</label>
            <input class="form-input" type="password" name="password" id="password">
        </div>

        <div class="form-group">
            <label class="form-label" for="password_confirmation">パスワード確認</label>
            <input class="form-input" type="password" name="password_confirmation" id="password_confirmation">
        </div>

        <button class="form-btn" type="submit">登録する</button>
    </form>

    <a class="auth-link" href="/login">ログインはこちらから</a>
</div>
@endsection