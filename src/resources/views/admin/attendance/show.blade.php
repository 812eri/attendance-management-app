@extends('layouts.admin')

@section('title', '勤怠詳細')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
@endsection

@section('content')
<div class="container">
    <h1 class="page-title">勤怠詳細</h1>

    @if ($isPending)
    <p class="error-message" style="text-align: center; margin-bottom: 20px;">*承認待ちのため修正はできません。</p>
    @endif

    <div class="detail-form-wrapper">
        <form action="{{ route('admin.attendance.update', $attendance->id) }}" method="post">
            @csrf

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
                    <input type="text" name="start_time" value="{{ old('start_time', $attendance->start_time ? \Carbon\Carbon::parse($attendance->start_time)->format('H:i') : '') }}" class="input-time" {{ $isPending ? 'disabled' : '' }}>
                    <span class="tilde">〜</span>
                    <input type="text" name="end_time" value="{{ old('end_time', $attendance->end_time ? \Carbon\Carbon::parse($attendance->end_time)->format('H:i') : '') }}" class="input-time" {{ $isPending ? 'disabled' : '' }}>

                    @error('start_time')
                    <p class="error-message">{{ $message }}</p>
                    @enderror
                    @error('end_time')
                    <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            @foreach ($attendance->rests as $index => $rest)
            <div class="form-group">
                <label class="form-label">休憩{{ $index > 0 ? $index + 1 : '' }}</label>
                <div class="form-content">
                    <input type="text" name="rests[{{ $rest->id }}][start_time]"
                            value="{{ old("rests.{$rest->id}.start_time", $rest->start_time ? \Carbon\Carbon::parse($rest->start_time)->format('H:i') : '') }}"
                            class="input-time" {{ $isPending ? 'disabled' : '' }}>
                    <span class="tilde">〜</span>
                    <input type="text" name="rests[{{ $rest->id }}][end_time]"
                            value="{{ old("rests.{$rest->id}.end_time", $rest->end_time ? \Carbon\Carbon::parse($rest->end_time)->format('H:i') : '') }}"
                            class="input-time" {{ $isPending ? 'disabled' : '' }}>
                    @error("rests.{$rest->id}.start_time")
                    <p class="error-message">{{ $message }}</p>
                    @enderror
                    @error("rests.{$rest->id}.end_time")
                    <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            @endforeach

            <div class="form-group">
                <label class="form-label">休憩{{ count($attendance->rests) + 1 }}</label>
                <div class="form-content">
                    <input type="text" name="new_rest[start_time]"
                            value="{{ old('new_rest.start_time') }}"
                            class="input-time" {{ $isPending ? 'disabled' : '' }}>
                    <span class="tilde">〜</span>
                    <input type="text" name="new_rest[end_time]"
                            value="{{ old('new_rest.end_time') }}"
                            class="input-time" {{ $isPending ? 'disabled' : '' }}>
                    @error('new_rest.start_time')
                    <p class="error-message">{{ $message }}</p>
                    @enderror
                    @error('new_rest.end_time')
                    <p class="error-message">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="form-group no-border">
                <label class="form-label">備考</label>
                <div class="form-content">
                    <textarea name="remarks" class="input-textarea" {{ $isPending ? 'disabled' : '' }}>{{ old('remarks', $attendance->remarks) }}</textarea>
            @error('remarks')
            <p class="error-message">{{ $message }}</p>
            @enderror
                </div>
            </div>
        </div>

        <div class="form-footer">
            <button type="submit" class="btn-submit" {{ $isPending ? 'disabled' : '' }}>修正</button>
        </div>
    </form>
</div>
@endsection