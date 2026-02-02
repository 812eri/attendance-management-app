<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\StampCorrectionRequest;
use App\Models\StampCorrectionRequestRest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => '管理者A',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        $user = User::create([
            'name' => '一般太郎',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role_id' => User::ROLE_USER,
            'email_verified_at' => now(),
        ]);

        for ($i = 1; $i <= 31; $i++) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->format('Y-m-d');

            if ($i === 1) {
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'date' => $date,
                    'start_time' => $dateStr . ' 09:00:00',
                    'end_time'   => $dateStr . ' 18:00:00',
                    'remarks'    => '通常勤務',
                ]);

                Rest::create([
                    'attendance_id' => $attendance->id,
                    'start_time'    => $dateStr . ' 12:00:00',
                    'end_time'      => $dateStr . ' 13:00:00',
                ]);

                $correctionRequest = StampCorrectionRequest::create([
                    'user_id' => $user->id,
                    'attendance_id' => $attendance->id,
                    'new_start_time' => '10:00:00',
                    'new_end_time'   => '19:00:00',
                    'new_remarks'    => '電車遅延のため修正申請',
                    'status'         => 'pending',
                ]);

                StampCorrectionRequestRest::create([
                    'stamp_correction_request_id' => $correctionRequest->id,
                    'new_break_start'=> '13:00:00',
                    'new_break_end'  => '14:00:00',
                ]);

                continue;
            }

            if (rand(1, 100) <= 30) {
                continue;
            }

            $attendance = Attendance::create([
                'user_id' => $user->id,
                'date' => $date,
                'start_time' => $dateStr . ' 09:00:00',
                'end_time'   => $dateStr . ' 18:00:00',
                'remarks'    => '通常勤務',
            ]);

            Rest::create([
                'attendance_id' => $attendance->id,
                'start_time'    => $dateStr . ' 12:00:00',
                'end_time'      => $dateStr . ' 13:00:00',
            ]);
        }

        echo "テストデータを投入しました。\n";
        echo "・今日はデータなし（勤務外表示）\n";
        echo "・昨日は出勤＆修正申請あり\n";
        echo "・過去データは09:00-18:00固定（ランダム休日あり）\n";

        $this->call([
            TestDataSeeder::class,
        ]);
        echo "スタッフ一覧用のダミーデータも投入しました。\n";
    }
}