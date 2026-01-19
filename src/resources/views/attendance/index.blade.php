@extends('layouts.app')

@section('title', '勤怠打刻')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-container">

    <div class="attendance-status">
        @if($status == 0)
            勤務外
        @elseif($status == 1)
            出勤中
        @elseif($status == 2)
            休憩中
        @else
            退勤済
        @endif
    </div>

    <div class="attendance-date">{{ $now_date }}</div>
    <div class="attendance-time">{{ $now_time }}</div>

    <div class="attendance-actions">
        @if($status == 0)
            <form method="post" action="{{ route('attendance.clockIn') }}">
                @csrf
                <button type="submit" class="attendance-btn">出勤</button>
            </form>

        @elseif($status ==1)
            <form method="post" action="{{ route('attendance.clockOut') }}">
                @csrf
                <button type="submit" class="attendance-btn">退勤</button>
            </form>

            <form method="post" action="{{ route('attendance.breakStart') }}">
                @csrf
                <button type="submit" class="attendance-btn is-white" >休憩入</button>
            </form>

        @elseif($status == 2)
            <form method="post" action="{{ route('attendance.breakEnd') }}">
                @csrf
                <button type="submit" class="attendance-btn is-white">休憩戻</button>
            </form>

        @else
            <p>お疲れ様でした。</p>
        @endif
    </div>

</div>
@endsection