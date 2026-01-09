<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Rest;
use Carbon\Carbon;
use App\Http\Requests\Admin\AttendanceUpdateRequest;

class AttendanceController extends Controller
{
    public function list(Request $request)
    {
        $date = $request->query('date', Carbon::today()->format('Y-m-d'));

        $currentDate = Carbon::parse($date);

        $attendances = Attendance::with('user')
        ->where('date', $currentDate->format('Y-m-d'))
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
        $attendance = Attendance::with(['user', 'rests', 'stampCorrectionRequests'])->findOrFail($id);

        $dt = Carbon::parse($attendance->date);
        $year = $dt->format('Y年');
        $date = $dt->format('n月j日');

        $isPending = $attendance->isPending();

        return view('admin.attendance.show', compact('attendance', 'year', 'date','isPending'));
    }

    public function update(AttendanceUpdateRequest $request, $id)
    {
        $attendance = Attendance::findOrFail($id);

        $baseDate = $attendance->date;

        $attendance->start_time = Carbon::parse("$baseDate {$request->start_time}");
        $attendance->end_time = Carbon::parse("$baseDate {$request->end_time}");
        $attendance->remarks = $request->remarks;
        $attendance->save();

        if ($request->has('rests')) {
            foreach ($request->rests as $restId => $restData) {
                $rest = Rest::find($restId);
                if ($rest) {
                    $rest->start_time = Carbon::parse("$baseDate {$restData['start_time']}");
                    $rest->end_time = Carbon::parse("$baseDate {$restData['end_time']}");
                    $rest->save();
                }
            }
        }

        if ($request->filled('new_rest.start_time') && $request->filled('new_rest.end_time')) {
            Rest::create([
                'attendance_id' => $attendance->id,
                'start_time' => Carbon::parse("$baseDate {$request->new_rest['start_time']}}"),
                'end_time' => Carbon::parse("$baseDate {$request->new_rest['end_time']}}"),
            ]);
        }

        return redirect()->route('admin.attendance.show', $id)->with('message', '勤怠情報を修正しました');
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
                    $date->format('Y/m/d'), // 日付
                    $attendance && $attendance->start_time ? Carbon::parse($attendance->start_time)->format('H:i') : '', // 出勤
                    $attendance && $attendance->end_time ? Carbon::parse($attendance->end_time)->format('H:i') : '', // 退勤
                    $restSum > 0 ? gmdate('H:i', $restSum * 60) : '', // 休憩
                    $workTime > 0 ? gmdate('H:i', $workTime * 60) : '', // 合計
                ];

                fputcsv($stream, $csvRow);
            }

            fclose($stream);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }}
