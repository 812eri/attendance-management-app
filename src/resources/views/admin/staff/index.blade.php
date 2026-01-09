@extends('layouts.admin')

@section('title', 'スタッフ一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">スタッフ一覧</h1>

    <div class="table-wrapper">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠<th>
                </tr>
            </thead>
                <tbody>
                @foreach ($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>
                        <a href="{{ route('admin.attendance.staff', $user->id) }}" class="btn-detail">詳細</a>
                    </td>
                </tr>
                @endforeach
                </tbody>
        </table>
    </div>
</div>
@endsection