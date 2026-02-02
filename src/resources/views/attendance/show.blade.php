@extends('layouts.app')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')
<div class="attendance-detail-container">
    <h1 class="page-title">勤怠詳細</h1>

    <form method="post"  action="{{ route('stamp_correction_request.store') }}" class="detail-form">
        @csrf
        <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">

        <div class="table-wrapper">
            <table class="detail-table">
                <tr>
                    <th>名前</th>
                    <td>
                        <span class="text-date">{{ $attendance->user->name }}</span>
                    </td>
                </tr>

                <tr>
                    <th>日付</th>
                    <td>
                        <div class="date-display">
                            <span class="text-data">{{ $year }}年</span>
                            <span class="text-data ml-20">{{ $month_day }}</span>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>出勤・退勤</th>
                    <td>
                        <div class="time-input-group">
                            <input type="time" name="new_start_time"
                                value="{{ $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '' }}"
                                class="form-control time-input"
                                @if($isPending) readonly @endif>
                            <span class="tilde">〜</span>
                            <input type="time" name="new_end_time"
                                value="{{ $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '' }}"
                                class="form-control time-input"
                                @if($isPending) readonly @endif>
                        </div>
                        @error('new_start_time')  <span class="text-danger">{{ $message }}</span> @enderror
                        @error('new_end_time') <span class="text-danger">{{ $message }}</span> @enderror
                    </td>
                </tr>

                @php
                    if ($isPending && $correctionRequest) {
                        $rests = $correctionRequest-≥stampCorrectionRequestRests;
                    } else {
                        $rests = $attendance->rests;
                    }
                @endphp

                @foreach($rests as $index => $rest)
                    <tr>
                        <th>
                            @if($index == 0)
                                休憩
                            @else
                                休憩{{ $index + 1 }}
                            @endif
                        </th>
                        <td>
                            <div class="time-input-group">
                                @php
                                    $startTime = $isPending ? $rest->new_break_start : $rest->start_time;
                                    $endTime = $isPending ? $rest->new_break_end : $rest->end_time;
                                @endphp

                                <input type="time" name="new_break_starts[]" 
                                    value="{{ \Carbon\Carbon::parse($rest->start_time)->format('H:i') }}" 
                                    class="form-control time-input" 
                                    @if($isPending) readonly @endif>
                                <span class="tilde">〜</span>
                                <input type="time" name="new_break_ends[]" 
                                    value="{{ \Carbon\Carbon::parse($rest->end_time)->format('H:i') }}" 
                                    class="form-control time-input" 
                                    @if($isPending) readonly @endif>
                            </div>
                        </td>
                    </tr>
                @endforeach

            @if(!$isPending)
                <tr>
                    <th>
                        @if(count($rests) == 0)
                            休憩
                        @else
                            休憩{{ count($rests) + 1 }}
                        @endif
                    </th>
                    <td>
                        <div class="time-input-group">
                            <input type="time" name="new_break_starts[]" class="form-control time-input">
                            <span class="tilde">〜</span>
                            <input type="time" name="new_break_ends[]" class="form-control time-input">
                        </div>
                    </td>
                </tr>
            @endif

                <tr>
                    <th>備考</th>
                    <td>
                        <textarea name="new_remarks" class="form-control textarea-input"
                                @if($isPending) readonly @endif>{{ $attendance->remarks }}</textarea>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer-area">
            @if($isPending)
                <p class="error-message">* 承認待ちのため修正はできません。</p>
            @else
                <button type="submit" class="btn-submit">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection