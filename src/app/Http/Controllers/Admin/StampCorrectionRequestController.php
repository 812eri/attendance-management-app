<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\DB;

class StampCorrectionRequestController extends Controller
{

    public function index(Request $request)
    {
        $status = $request->input('tab', 'pending');

        $requests = StampCorrectionRequest::where('status', $status)
            ->with(['user', 'attendance'])
            ->get();

        return view('admin.correction_request.index', compact('requests', 'status'));
    }

    public function approveView($attendance_correct_request_id)
    {
        $request = StampCorrectionRequest::with(['user', 'attendance'])->findOrFail($attendance_correct_request_id);

        $dt = \Carbon\Carbon::parse($request->attendance->date);
        $year = $dt->format('Y年');
        $date = $dt->format('n月j日');

        return view('admin.correction_request.approve', compact('request', 'year', 'date'));
    }

    public function approve($attendance_correct_request_id)
    {
        DB::transaction(function () use ($attendance_correct_request_id) {
            $request = StampCorrectionRequest::findOrFail($attendance_correct_request_id);
            $attendance = Attendance::findOrFail($request->attendance_id);

            $baseDate = \Carbon\Carbon::parse($attendance->date)->format('Y-m-d');

            $newStartTime = $request->new_start_time 
                ? \Carbon\Carbon::parse($request->new_start_time)->format('H:i:s') 
                : null;

            $newEndTime = $request->new_end_time 
                ? \Carbon\Carbon::parse($request->new_end_time)->format('H:i:s') 
                : null;

            $newBreakStart = $request->new_break_start 
                ? \Carbon\Carbon::parse($request->new_break_start)->format('H:i:s') 
                : null;

            $newBreakEnd = $request->new_break_end 
                ? \Carbon\Carbon::parse($request->new_break_end)->format('H:i:s') 
                : null;

            $attendance->start_time = $newStartTime ? "{$baseDate} {$newStartTime}" : null;
            $attendance->end_time = $newEndTime ? "{$baseDate} {$newEndTime}" : null;
            $attendance->remarks = $request->new_remarks;
            $attendance->save();

            $attendance->rests()->delete();

            if ($newBreakStart && $newBreakEnd) {
                \App\Models\Rest::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => "{$baseDate} {$newBreakStart}",
                    'end_time' => "{$baseDate} {$newBreakEnd}",
                ]);
            }

            $request->status = 'approved';
            $request->save();
        });

        return redirect()->route('admin.stamp_correction_request.index')->with('message', '修正申請を承認しました');
    }
}
