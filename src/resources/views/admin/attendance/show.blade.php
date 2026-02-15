@extends('layouts.admin')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">勤怠詳細</h1>

    <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="post">
        @csrf
        <div class="detail-form-wrapper">
            <div class="form-group">
                <label class="form-label">名前</label>
                <div class="form-content text-content">
                    {{ $attendance->user->name }}
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
                    @php
                        $startTime = $isPending ? $correctionRequest->new_start_time : $attendance->start_time;
                        $endTime = $isPending ? $correctionRequest->new_end_time : $attendance->end_time;
                    @endphp

                    <input type="text" name="start_time"
                        value="{{ old('start_time', $startTime ? \Carbon\Carbon::parse($startTime)->format('H:i') : '') }}"
                        class="input-time"
                        onfocus="this.type='time'"
                        onblur="if(this.value==''){this.type='text'}"
                        {{ $isPending ? 'disabled' : '' }}>

                    <span class="tilde">〜</span>

                    <input type="text" name="end_time"
                        value="{{ old('end_time', $endTime ? \Carbon\Carbon::parse($endTime)->format('H:i') : '') }}"
                        class="input-time"
                        onfocus="this.type='time'"
                        onblur="if(this.value==''){this.type='text'}"
                        {{ $isPending ? 'disabled' : '' }}>

                    @error('start_time')
                    <p class="error-message">{{ $message }}</p>
                    @enderror
                    @error('end_time')
                    <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @php
            if ($isPending && $correctionRequest) {
                $displayRests = $correctionRequest->stampCorrectionRequestRests;
            } else {
                $displayRests = $attendance->rests;
            }
            @endphp

            @foreach ($displayRests as $index => $rest)
            <div class="form-group">
                <label class="form-label">休憩{{ $index > 0 ? $index + 1 : '' }}</label>
                <div class="form-content">
                    @php
                        if ($isPending) {
                            $restStart = $rest->new_break_start;
                            $restEnd = $rest->new_break_end;
                            $restId = $index;
                        } else {
                            $restStart = $rest->start_time;
                            $restEnd = $rest->end_time;
                            $restId = $rest->id;
                        }
                    @endphp

                    <input type="text" name="rests[{{ $isPending ? $index : $restId }}][start_time]"
                            value="{{ \Carbon\Carbon::parse($restStart)->format('H:i') }}"
                            class="input-time"
                            onfocus="this.type='time'"
                            onblur="if(this.value==''){this.type='text'}"
                            {{ $isPending ? 'disabled' : '' }}>

                    <span class="tilde">〜</span>

                    <input type="text" name="rests[{{ $isPending ? $index : $restId }}][end_time]"
                            value="{{ \Carbon\Carbon::parse($restEnd)->format('H:i') }}"
                            class="input-time"
                            onfocus="this.type='time'"
                            onblur="if(this.value==''){this.type='text'}"
                            {{ $isPending ? 'disabled' : '' }}>

                    @if(!$isPending)
                        @error("rests.{$restId}.start_time")
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                        @error("rests.{$restId}.end_time")
                        <p class="error-message">{{ $message }}</p>
                        @enderror
                    @endif
                </div>
            </div>
            @endforeach

            @if (!$isPending)
            <div class="form-group">
                <label class="form-label">休憩{{ count($attendance->rests) + 1 }}</label>
                <div class="form-content">
                    <input type="text" name="new_rest[start_time]"
                            value="{{ old('new_rest.start_time') }}"
                            class="input-time"
                            onfocus="this.type='time'"
                            onblur="if(this.value==''){this.type='text'}">

                    <span class="tilde">〜</span>

                    <input type="text" name="new_rest[end_time]"
                            value="{{ old('new_rest.end_time') }}"
                            class="input-time"
                            onfocus="this.type='time'"
                            onblur="if(this.value==''){this.type='text'}">

                    @error('new_rest.start_time')
                    <p class="error-message">{{ $message }}</p>
                    @enderror
                    @error('new_rest.end_time')
                    <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            @endif

            <div class="form-group no-border">
                <label class="form-label">備考</label>
                <div class="form-content">
                    <textarea name="remarks" class="input-textarea" {{ $isPending ? 'disabled' : '' }}>{{ old('remarks', $isPending ? $correctionRequest->new_remarks : $attendance->remarks) }}</textarea>

                    @error('remarks')
                    <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-footer">
            @if ($isPending)
                <p class="error-message" style="text-align: right; width: 100%; margin: 0;">* 承認待ちのため修正はできません。</p>
            @else
                <button type="submit" class="btn-submit">修正</button>
            @endif
        </div>
    </form>
</div>
@endsection