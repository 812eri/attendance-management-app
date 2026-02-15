<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrectionRequest;
use App\Models\StampCorrectionRequestRest;
use App\Http\Requests\StoreStampCorrectionRequest;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StampCorrectionRequestController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->input('tab', 'pending');

        $query = StampCorrectionRequest::where('user_id', $user->id);

        if ($tab === 'approved') {
            $query->where('status', 'approved');
        } else {
            $query->where('status', 'pending');
        }

        $requests = $query->with(['attendance', 'stampCorrectionRequestRests'])->latest()->get();

        return view('stamp_correction_request.index', compact('requests', 'tab'));
    }

    public function store(StoreStampCorrectionRequest $request)
    {
        $validated = $request->validated();

        $attendance = Attendance::findOrFail($request->attendance_id);

        $attendanceDate = Carbon::parse($attendance->start_time)->format('Y-m-d');

    DB::transaction(function () use ($request, $attendance, $attendanceDate) {
        $correctionRequest = StampCorrectionRequest::create([
            'user_id' => Auth::id(),
            'attendance_id' => $attendance->id,
            'status' => 'pending',
            'new_start_time' => $attendanceDate . ' ' . $request->new_start_time,
            'new_end_time'   => $attendanceDate . ' ' . $request->new_end_time,
            'new_remarks'    => $request->new_remarks,
        ]);

        if ($request->has('new_break_starts')) {
            foreach ($request->new_break_starts as $index => $startTime) {
                $endTime = $request->new_break_ends[$index] ?? null;

                if ($startTime && $endTime) {
                    StampCorrectionRequestRest::create([
                        'stamp_correction_request_id' => $correctionRequest->id,
                        'new_break_start' => $attendanceDate . ' ' . $startTime,
                        'new_break_end'   => $attendanceDate . ' ' . $endTime,
                    ]);
                }
            }
        }
    });

    return redirect()->route('attendance.show', ['id' => $request->attendance_id]);
}
}
