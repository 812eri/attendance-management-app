<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\StampCorrectionRequest;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
                                ->where('date', $today)
                                ->first();

        if (!$attendance) {
            $status = 0;
        } else {
            if ($attendance->end_time) {
                $status = 3;
            } else {
                $ongoingRest = $attendance->rests()->whereNull('end_time')->first();

                if ($ongoingRest) {
                    $status = 2;
                } else {
                    $status = 1;
                }
            }
        }

        $now_date = Carbon::now()->format('Y年m月d日') . '(' . Carbon::now()->isoFormat('ddd') . ')';
        $now_time = Carbon::now()->format('H:i');

        return view('attendance.index', compact('user', 'status', 'now_date', 'now_time'));
    }

    public function clockIn()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $oldAttendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();
        if ($oldAttendance) {
            return redirect()->back()->with('message', 'すでに出勤しています');
        }

        Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'start_time' => Carbon::now(),
        ]);

        return redirect()->back();
    }


    public function clockOut()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if ($attendance) {
            $attendance->update([
                'end_time' => Carbon::now(),
            ]);
        }

        return redirect()->back();
    }

    public function breakStart()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if ($attendance) {
            Rest::create([
                'attendance_id' => $attendance->id,
                'start_time' => Carbon::now(),
            ]);
        }

        return redirect()->back();
    }
    public function breakEnd()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $attendance = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        if ($attendance) {
            $rest = $attendance->rests()->whereNull('end_time')->first();
            if ($rest) {
                $rest->update([
                    'end_time' => Carbon::now(),
                ]);
            }
        }

        return redirect()->back();
    }

    public function list(Request $request)
    {
        $user = Auth::user();

        $currentDate = $request->input('month')
            ? Carbon::parse($request->input('month'))
            : Carbon::now()->startOfMonth();

        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();
        $days = CarbonPeriod::create($startOfMonth, $endOfMonth)->toArray();

        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy('date');

        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m');
        $currentMonth = $currentDate->format('Y/m');

        return view('attendance.list', compact(
            'user',
            'days',
            'attendances',
            'currentMonth',
            'prevMonth',
            'nextMonth'
        ));
    }

    public function show($id)
    {
        $user = Auth::user();

        $attendance = Attendance::with('rests')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        $correctionRequest = StampCorrectionRequest::with('stampCorrectionRequestRests')
            ->where('user_id', $user->id)
            ->where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        $isPending = !is_null($correctionRequest);

        $year = Carbon::parse($attendance->date)->format('Y');
        $month_day = Carbon::parse($attendance->date)->format('n月j日');

        return view('attendance.show', compact('attendance', 'year', 'month_day', 'isPending', 'correctionRequest'));
    }
}
