<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Rest;
use App\Models\StampCorrectionRequest;
use App\Models\StampCorrectionRequestRest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Requests\Admin\AttendanceUpdateRequest;

class AttendanceController extends Controller
{
    public function list(Request $request)
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));

        $currentDate = Carbon::parse($date);

        $attendances = Attendance::with('user')
        ->whereDate('date', $currentDate)
        ->get();

        $viewData = [
            'currentDate' => $currentDate->format('Y/m/d'),
            'displayDate' => $currentDate->format('Y年n月j日'),
            'prevDate' => $currentDate->copy()->subDay()->format('Y-m-d'),
            'nextDate' => $currentDate->copy()->addDay()->format('Y-m-d'),
            'attendances' => $attendances,
        ];

        return view('admin.attendance.list', $viewData);
    }

    public function show($id)
    {
        $attendance = Attendance::with(['rests', 'user'])->findOrFail($id);

        $correctionRequest = StampCorrectionRequest::with('stampCorrectionRequestRests')
            ->where('attendance_id', $attendance->id)
            ->where('status', 'pending')
            ->first();

        $isPending = !is_null($correctionRequest);

        $dt = Carbon::parse($attendance->date);
        $year = $dt->format('Y年');
        $date = $dt->format('n月j日');

        return view('admin.attendance.show', compact('attendance', 'correctionRequest', 'isPending', 'year', 'date'));    }

    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $attendance) {
            $correctionRequest = \App\Models\StampCorrectionRequest::create([
                'user_id' => $attendance->user_id,
                'attendance_id' => $attendance->id,
                'new_start_time' => $request->start_time,
                'new_end_time' => $request->end_time,
                'new_remarks' => $request->remarks,
                'status' => 'pending',
            ]);

            if ($request->has('rests')) {
                foreach ($request->rests as $restData) {
                    \App\Models\StampCorrectionRequestRest::create([
                        'stamp_correction_request_id' => $correctionRequest->id,
                        'new_break_start' => $restData['start_time'],
                        'new_break_end' => $restData['end_time'],
                    ]);
                }
            }

            if ($request->filled('new_rest.start_time') && $request->filled('new_rest.end_time')) {
                \App\Models\StampCorrectionRequestRest::create([
                    'stamp_correction_request_id' => $correctionRequest->id,
                    'new_break_start' => $request->new_rest['start_time'],
                    'new_break_end' => $request->new_rest['end_time'],
                ]);
            }
        });

        return redirect()->route('admin.attendance.show', $id)
            ->with('message', '修正申請を作成しました。承認待ちとなります。');
    }

    public function staffList($id, Request $request)
    {
        $user = User::findOrFail($id);

        $currentDate = $request->input('month')
            ? Carbon::parse($request->input('month') . '-01')
            : Carbon::now();

            $yearMonth = $currentDate->format('Y-m');

            $attendances = Attendance::where('user_id', $id)
            ->where('date', 'like', $yearMonth . '%')
            ->orderBy('date', 'asc')
            ->get();

            $daysInMonth = $currentDate->daysInMonth;
            $calendar = [];

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $date = $currentDate->copy()->day($i);
                $dateStr = $date->format('Y-m-d');

                $attendance = $attendances->firstWhere('date', $dateStr);

                $restSum = 0;
                if ($attendance) {
                    foreach ($attendance->rests as $rest) {
                        if ($rest->start_time && $rest->end_time) {
                            $start = Carbon::parse($rest->start_time);
                            $end = Carbon::parse($rest->end_time);
                            $restSum += $start->diffInMinutes($end);
                        }
                    }
                }

                $workTime = 0;
                if ($attendance && $attendance->start_time && $attendance->end_time) {
                    $start = Carbon::parse($attendance->start_time);
                    $end = Carbon::parse($attendance->end_time);
                    $diff = $start->diffInMinutes($end);
                    $workTime = $diff - $restSum;
                }

                $calendar[] = [
                    'date' => $date->format('m/d') . '(' . $date->isoFormat('ddd') . ')',
                    'start_time' => $attendance && $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '',
                    'end_time' => $attendance && $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '',
                    'rest_sum' => $restSum > 0 ? gmdate('H:i', $restSum * 60) : '',
                    'work_time' => $workTime > 0 ? gmdate('H:i', $workTime * 60) : '',
                    'attendance_id' => $attendance ? $attendance->id : null,
                ];
            }

            $prevMonth = $currentDate->copy()->subMonth()->format('Y-m');
            $nextMonth = $currentDate->copy()->addMonth()->format('Y-m');

            return view('admin.attendance.staff_list', compact('user', 'calendar', 'currentDate', 'prevMonth', 'nextMonth'));
    }

    public function exportCsv($id, Request $request)
    {
        $user = User::findOrFail($id);

        $currentDate = $request->input('month') 
            ? Carbon::parse($request->input('month') . '-01') 
            : Carbon::now();

        $yearMonth = $currentDate->format('Y-m');

        $attendances = Attendance::where('user_id', $id)
            ->where('date', 'like', $yearMonth . '%')
            ->orderBy('date', 'asc')
            ->get();

        $daysInMonth = $currentDate->daysInMonth;

        $filename = 'attendance_' . $currentDate->format('Y-m') . '_' . $user->name . '.csv';

        return response()->streamDownload(function () use ($attendances, $daysInMonth, $currentDate) {
            $stream = fopen('php://output', 'w');

            fwrite($stream, "\xEF\xBB\xBF");

            fputcsv($stream, ['日付', '出勤', '退勤', '休憩', '合計']);

            for ($i = 1; $i <= $daysInMonth; $i++) {
                $date = $currentDate->copy()->day($i);
                $dateStr = $date->format('Y-m-d');
                $attendance = $attendances->firstWhere('date', $dateStr);

                $restSum = 0;
                if ($attendance) {
                    foreach ($attendance->rests as $rest) {
                        if ($rest->start_time && $rest->end_time) {
                            $start = Carbon::parse($rest->start_time);
                            $end = Carbon::parse($rest->end_time);
                            $restSum += $start->diffInMinutes($end);
                        }
                    }
                }

                $workTime = 0;
                if ($attendance && $attendance->start_time && $attendance->end_time) {
                    $start = Carbon::parse($attendance->start_time);
                    $end = Carbon::parse($attendance->end_time);
                    $diff = $start->diffInMinutes($end);
                    $workTime = $diff - $restSum;
                }

                $csvRow = [
                    $date->format('Y/m/d'),
                    $attendance && $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '',
                    $attendance && $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '',
                    $restSum > 0 ? gmdate('H:i', $restSum * 60) : '',
                    $workTime > 0 ? gmdate('H:i', $workTime * 60) : '',
                ];

                fputcsv($stream, $csvRow);
            }

            fclose($stream);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }}
