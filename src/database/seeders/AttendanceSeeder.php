<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = User::where('email', 'user@example.com')->first();

        if (!$user) {
            $this->command->error('ユーザーが存在しません。先にユーザーを作成してください。');
            return;
        }

        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $exists = Attendance::where('user_id', $user->id)
                ->where('date', $currentDate->format('Y-m-d'))
                ->exists();

                if (!$exists) {
                    $startTime = $currentDate->copy()->setTime(9, 0, 0);
                    $endTime = $currentDate->copy()->setTime(18, 0, 0);

                    $attendance = Attendance::create([
                        'user_id' => $user->id,
                        'date' => $currentDate->format('Y-m-d'),
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'remarks' => 'テストデータ',
                    ]);

                    $restStart = $currentDate->copy()->setTime(12, 0, 0);
                    $restEnd = $currentDate->copy()->setTime(13, 0, 0);

                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'start_time' => $restStart,
                        'end_time' => $restEnd,
                    ]);
                }

                $currentDate->addDay();
        }
    }
}
