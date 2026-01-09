@extends('layouts.admin')

@section('title', $user->name . 'さんの勤怠')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">{{ $user->name }}さんの勤怠</h1>

    <div class="date-nav">
        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $prevMonth]) }}" class="date-nav__link prev">← 前月</a>

        <span class="date-nav__current">
            <img src="{{ asset('images/calendar.png') }}" alt="calendar" class="calendar-icon">
            {{ $currentDate->format('Y/m') }}
        </span>

        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth]) }}" class="date-nav__link next">翌月 →</a>
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
                @foreach($calendar as $day)
                <tr>
                    <td>{{ $day['date'] }}</td>
                    <td>{{ $day['start_time'] }}</td>
                    <td>{{ $day['end_time'] }}</td>
                    <td>{{ $day['rest_sum'] }}</td>
                    <td>{{ $day['work_time'] }}</td>
                    <td>
                        @if($day['attendance_id'])
                        <a href="{{ route('admin.attendance.show', $day['attendance_id']) }}" class="btn-detail">詳細</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="csv-btn-wrapper" style="text-align: right; max-width: 800px; margin: 20px auto;">
        <form action="{{ route('admin.attendance.csv', $user->id) }}" method="get">
            <input type="hidden" name="month" value="{{ $currentDate->format('Y-m') }}">

            <button type="submit" class="btn-submit" style="border-radius: 0;">CSV出力</button>
        </form>
    </div>
</div>
@endsection