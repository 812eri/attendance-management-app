@extends('layouts.admin')

@section('title', '勤怠一覧')

@section('content')
<div class="container">
    <h1 class="page-title">
        {{ $displayDate }}の勤怠
    </h1>

    <div class="date-nav">
        <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" class="date-nav__link prev">
            &larr; 前日
        </a>

        <div class="date-nav__current">
            <img src="{{ asset('images/calendar.png') }}" alt="カレンダー" class="calendar-icon">
            <span>{{ $currentDate }}</span>
        </div>

        <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="date-nav__link next">
            翌日 &rarr;
        </a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th class="align-left">名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($attendances as $attendance)
            <tr>
                <td class="align-left">{{ $attendance->user->name ?? '削除済みユーザー' }}</td>
                <td>{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}</td>
                <td>
                    @if ($attendance->end_time)
                        {{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}
                    @else
                        @endif
                </td>
                <td>
                    {{ $attendance->rest_sum === '00:00' ? '' : $attendance->rest_sum }}
                </td>
                <td>
                    {{ $attendance->work_time === '00:00' ? '' : $attendance->work_time }}
                </td>
                <td>
                    <a href="{{ route('admin.attendance.show', ['id' => $attendance->id]) }}" class="btn-detail">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection