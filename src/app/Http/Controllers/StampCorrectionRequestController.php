<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StampCorrectionRequest;
use App\Models\Attendance;
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

        $requests = $query->latest()->get();

        return view('stamp_correction_request.index', compact('requests', 'tab'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'attendance_id' => 'required|exists:attendance,id',
            'remarks' => 'required',
        ]);

        $attendance = Attendance::findOrFail($request->attendance_id);

        DB::transaction(function () use ($request, $attendance) {
            StampCorrectionRequest::create([
                'user_id' => Auth::id(),
                'attendance_id' => $attendance->id,
                'status' => 'pending',
                'new_start_time' => $request->start_time,
                'new_end_time' => $request->end_time,
                'new_remarks' => $request->remarks,
            ]);
        });

        return redirect()->route('stamp_correction_request.index', ['tab' => 'pending']);
    }
}
