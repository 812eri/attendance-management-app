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

                        @php
                            if ($isPending && $correctionRequest) {
                                // 申請中のデータを使う
                                $startTimeValue = $correctionRequest->new_start_time;
                                $endTimeValue   = $correctionRequest->new_end_time;
                            } else {
                                // 元の勤怠データを使う
                                $startTimeValue = $attendance->start_time;
                                $endTimeValue   = $attendance->end_time;
                            }
                        @endphp

                        <div class="time-input-group">
                            <input type="text" name="new_start_time"
                                value="{{ old('new_start_time', $startTimeValue ? \Carbon\Carbon::parse($startTimeValue)->format('H:i') : '') }}"
                                class="form-control time-input"
                                onfocus="this.type='time'"
                                onblur="if(this.value==''){this.type='text'}"
                                @if($isPending) readonly @endif>

                            <span class="tilde">〜</span>

                            <input type="text" name="new_end_time"
                                value="{{ old('new_end_time', $endTimeValue ? \Carbon\Carbon::parse($endTimeValue)->format('H:i') : '') }}"
                                class="form-control time-input"
                                onfocus="this.type='time'"
                                onblur="if(this.value==''){this.type='text'}"
                                @if($isPending) readonly @endif>
                        </div>
                        @error('new_start_time')  <span class="text-danger">{{ $message }}</span> @enderror
                        @error('new_end_time') <span class="text-danger">{{ $message }}</span> @enderror
                    </td>
                </tr>

               @php
                    if ($isPending && $correctionRequest) {
                        $rests = $correctionRequest->stampCorrectionRequestRests;
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
                                    $initialStart = $isPending ? $rest->new_break_start : $rest->start_time;
                                    $initialEnd   = $isPending ? $rest->new_break_end   : $rest->end_time;

                                    $formattedStart = $initialStart ? \Carbon\Carbon::parse($initialStart)->format('H:i') : '';
                                    $formattedEnd   = $initialEnd   ? \Carbon\Carbon::parse($initialEnd)->format('H:i')   : '';
                                @endphp

                                <input type="text" name="new_break_starts[]"
                                    value="{{ old('new_break_starts.'.$index, $formattedStart) }}"
                                    class="form-control time-input"
                                    onfocus="this.type='time'"
                                    onblur="if(this.value==''){this.type='text'}"
                                    @if($isPending) readonly @endif>

                                <span class="tilde">〜</span>

                                <input type="text" name="new_break_ends[]"
                                    value="{{ old('new_break_ends.'.$index, $formattedEnd) }}"
                                    class="form-control time-input"
                                    onfocus="this.type='time'"
                                    onblur="if(this.value==''){this.type='text'}"
                                    @if($isPending) readonly @endif>
                            </div>

                            @if($errors->has('new_break_starts.'.$index) || $errors->has('new_break_ends.'.$index))
                                <div class="text-danger">
                                    {{ $errors->first('new_break_starts.'.$index) ?: $errors->first('new_break_ends.'.$index) }}
                                </div>
                            @endif
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
                            <input type="text" name="new_break_starts[]"
                                value="{{ old('new_break_starts.'.count($rests)) }}"
                                class="form-control time-input"
                                onfocus="this.type='time'"
                                onblur="if(this.value==''){this.type='text'}">

                            <span class="tilde">〜</span>

                            <input type="text" name="new_break_ends[]"
                                value="{{ old('new_break_ends.'.count($rests)) }}"
                                class="form-control time-input"
                                onfocus="this.type='time'"
                                onblur="if(this.value==''){this.type='text'}">
                        </div>

                        @php $newIndex = count($rests); @endphp
                        @if($errors->has('new_break_starts.'.$newIndex) || $errors->has('new_break_ends.'.$newIndex))
                            <div class="text-danger">
                                {{ $errors->first('new_break_starts.'.$newIndex) ?: $errors->first('new_break_ends.'.$newIndex) }}
                            </div>
                        @endif
                    </td>
                </tr>
            @endif

                <tr>
                    <th>備考</th>
                    <td>

                        @php
                            if ($isPending && $correctionRequest) {
                                $remarksValue = $correctionRequest->new_remarks;
                            } else {
                                $remarksValue = $attendance->remarks;
                            }
                        @endphp

                        <textarea name="new_remarks" class="form-control textarea-input"
                                @if($isPending) readonly @endif>{{ old('new_remarks', $remarksValue) }}</textarea>

                        @error('new_remarks')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
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