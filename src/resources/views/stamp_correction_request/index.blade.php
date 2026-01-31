@extends('layouts.app')

@section('title', '申請一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/request_list.css') }}">
@endsection

@section('content')
<div class="request_list-container">
    <h1 class="page-title">申請一覧</h1>

    <div class="tab-container">
        <a href="{{ route('stamp_correction_request.index', ['tab' => 'pending']) }}"
            class="tab-item {{ $tab == 'pending' ? 'active' : '' }}">
            承認待ち
        </a>
        <a href="{{ route('stamp_correction_request.index', ['tab' => 'approved']) }}"
            class="tab-item {{ $tab == 'approved' ? 'active' : '' }}">
            承認済み
        </a>
    </div>

    <div class="table-wrapper">
        <table class="request-table">
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
                @foreach($requests as $req)
                    <tr>
                        <td>
                            @if($req->status == 'pending')
                                承認待ち
                            @elseif($req->status == 'approved')
                                承認済み
                            @else
                                {{ $req->status }}
                            @endif
                        </td>

                        <td>{{ $req->user->name }}</td>

                        <td>
                            {{ \Carbon\Carbon::parse($req->attendance->date)->format('Y/m/d') }}
                        </td>

                        <td>{{ $req->new_remarks }}</td>

                        <td>{{ $req->created_at->format('Y/m/d') }}</td>

                        <td>
                            <a href="{{ route('attendance.show', $req->attendance->id) }}" class="btn-detail">詳細</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection