<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $usersData = [
            ['name' => '山田 太郎', 'email' => 'yamada@example.com'],
            ['name' => '西 伶奈',   'email' => 'nishi@example.com'],
            ['name' => '増田 一世', 'email' => 'masuda@example.com'],
            ['name' => '山本 敬吉', 'email' => 'yamamoto@example.com'],
            ['name' => '秋田 朋美', 'email' => 'akita@example.com'],
            ['name' => '中西 教夫', 'email' => 'nakanishi@example.com'],
        ];

        foreach ($usersData as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password123'),
                    'role_id' => User::ROLE_USER,
                    'email_verified_at' => now(),
                ]
            );

            for ($i = 0; $i < 30; $i++) {
                $targetDate = Carbon::today()->subDays($i);
                
                if (rand(1, 100) <= 30) {
                    continue;
                }

                $startTime = $targetDate->copy()->setTime(9, 0, 0);
                $endTime   = $targetDate->copy()->setTime(18, 0, 0);

                $attendance = Attendance::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'date'    => $targetDate->format('Y-m-d')
                    ],
                    [
                        'start_time' => $startTime,
                        'end_time'   => $endTime,
                        'remarks'    => '自動生成データ',
                    ]
                );

                if ($attendance->wasRecentlyCreated) {
                    $restStart = $targetDate->copy()->setTime(12, 0, 0);
                    $restEnd   = $targetDate->copy()->setTime(13, 0, 0);

                    Rest::create([
                        'attendance_id' => $attendance->id,
                        'start_time'    => $restStart,
                        'end_time'      => $restEnd,
                    ]);
                }
            }
        }
    }
}