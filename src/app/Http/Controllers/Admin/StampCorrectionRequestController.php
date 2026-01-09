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

    public function index()
    {
        $pendingRequests = StampCorrectionRequest::where('status', 'pending')
            ->with(['user', 'attendance'])->get();

        $approvedRequests = StampCorrectionRequest::where('status', 'approved')
            ->with(['user', 'attendance'])->get();

        return view('admin.correction_request.index', compact('pendingRequests', 'approvedRequests'));
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

            $baseDate = $attendance->date;

            $attendance->start_time = $request->new_start_time ? $baseDate . ' ' . $request->new_start_time : null;
            $attendance->end_time = $request->new_end_time ? $baseDate . ' ' . $request->new_end_time : null;
            $attendance->remarks = $request->new_remarks;
            $attendance->save();

            $attendance->rests()->delete();

            if ($request->new_break_start && $request->new_break_end) {
                Rest::create([
                    'attendance_id' => $attendance->id,
                    'start_time' => $baseDate . ' ' . $request->new_break_start,
                    'end_time' => $baseDate . ' ' .$request->new_break_end,
                ]);
            }

            $request->status = 'approved';
            $request->save();
        });

        return redirect()->route('admin.stamp_correction_request.index')->with('message', '修正申請を承認しました');
    }
}
