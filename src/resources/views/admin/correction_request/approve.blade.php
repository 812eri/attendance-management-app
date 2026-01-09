@extends('layouts.admin')

@section('title', '修正申請承認')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">勤怠詳細</h1>

    <div class="detail-form-wrapper">
        @if($request->status === 'pending')
        <form action="{{ route('admin.stamp_correction_request.approve.action', $request->id) }}" method="post">
            @csrf
        @endif

            <div class="form-group">
                <label class="form-label">名前</label>
                <div class="form-content text-content">
                    {{ $request->user->name }}
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">日付</label>
                <div class="form-content text-content">
                    <span class="date-part">{{ $year }}</span>
                    <span class="date-part">{{ $date }}</span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">出勤・退勤</label>
                <div class="form-content">
                    <div class="time-text">{{ \Carbon\Carbon::parse($request->new_start_time)->format('H:i') }}</div>
                    <span class="tilde">〜</span>
                    <div class="time-text">{{ \Carbon\Carbon::parse($request->new_end_time)->format('H:i') }}</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">休憩</label>
                <div class="form-content">
                    <div class="time-text">{{ \Carbon\Carbon::parse($request->new_break_start)->format('H:i') }}</div>
                    <span class="tilde">〜</span>
                    <div class="time-text">{{ \Carbon\Carbon::parse($request->new_break_end)->format('H:i') }}</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">休憩2</label>
                <div class="form-content">
                </div>
            </div>

            <div class="form-group no-border">
                <label class="form-label">備考</label>
                <div class="form-content text-content" style="display: block; max-width: 100%;">
                    {{ $request->new_remarks }}
                </div>
            </div>
        </div>


            <div class="form-footer">
                @if($request->status === 'pending')
                    <button type="submit" class="btn-submit">承認</button>
                @else
                    <button type="button" class="btn-submit btn-gray" disabled>承認済み</button>
                @endif
            </div>

            @if ($request->status === 'pending')
        </form>
            @endif
</div>
@endsection