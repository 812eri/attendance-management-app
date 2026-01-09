@extends('layouts.guest')

@section('title', 'メールアドレス認証')

@section('css')
<link rel="stylesheet" href="{{ asset('css/auth/verify-email.css') }}">
@endsection

@section('content')
<div class="verify-email-container">
    <div class="message-box">
        <p>登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。</p>
    </div>

    <div class="verify-email__action">
        <a href="http://localhost:8025" target="_blank" class="verify-email__button-box">
            認証はこちらから
        </a>
    </div>

        <div class="verify-email__action">
            <form method="post" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="verify-email__button-text">
                    認証メールを再送する
                </button>
            </form>
        </div>

</div>
@endsection