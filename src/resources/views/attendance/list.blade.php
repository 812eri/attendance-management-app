@extends('layouts.app')

@section('title', '勤怠一覧')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
<div class="attendance-list-container">

    <h1 class="page-title">勤怠一覧</h1>

    <div class="date-nav">
        <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="date-nav__link prev">
            &larr; 前月
        </a>

        <div class="date-nav__current">
            <img src="{{ asset('images/calendar.png') }}" alt="カレンダー" class="calendar-icon">
            <span>{{ $currentMonth }}</span>
        </div>

        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="date-nav__link next">
            翌月 &rarr;
        </a>
    </div>

    <div class="table-wrapper">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach($days as $day)
                    @php
                        $dateStr = $day->format('Y-m-d');
                        $attendance = $attendances->get($dateStr);
                    @endphp
                    <tr>
                        <td>{{ $day->isoFormat('MM/DD(ddd)') }}</td>

                        <td>
                            @if($attendance && $attendance->start_time)
                                {{ \Carbon\Carbon::parse($attendance->start_time)->format('H:i') }}
                            @endif
                        </td>

                        <td>
                            @if($attendance && $attendance->end_time)
                                {{ \Carbon\Carbon::parse($attendance->end_time)->format('H:i') }}
                            @endif
                        </td>

                        <td>
                            @if($attendance)
                                {{ $attendance->rest_sum }}
                            @endif
                        </td>

                        <td>
                            @if($attendance)
                                {{ $attendance->work_time }}
                            @endif
                        </td>

                        <td>
                            @if($attendance)
                                <a href="{{ route('attendance.show', $attendance->id) }}" class="btn-detail">詳細</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection