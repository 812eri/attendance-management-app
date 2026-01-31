@extends('layouts.admin')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">申請一覧</h1>

    <div class="tab-menu">
        <a href="{{ route('admin.stamp_correction_request.index', ['tab' => 'pending']) }}" 
           class="tab-btn {{ $status === 'pending' ? 'active' : '' }}">
            承認待ち
        </a>
        <a href="{{ route('admin.stamp_correction_request.index', ['tab' => 'approved']) }}" 
           class="tab-btn {{ $status === 'approved' ? 'active' : '' }}">
            承認済み
        </a>
    </div>

    <div class="tab-content active">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $request)
                <tr>
                    <td>
                        {{ $request->status === 'pending' ? '承認待ち' : '承認済み' }}
                    </td>
                    <td>{{ $request->user->name }}</td>
                    <td>
                        @if($request->attendance)
                            {{ \Carbon\Carbon::parse($request->attendance->date)->format('Y/m/d') }}
                        @else
                            <span style="color:red;">データなし</span>
                        @endif
                    </td>
                    <td>{{ $request->new_remarks }}</td>
                    <td>{{ $request->created_at->format('Y/m/d') }}</td>
                    <td>
                        <a href="{{ route('admin.stamp_correction_request.approve', $request->id) }}" class="btn-detail">詳細</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection