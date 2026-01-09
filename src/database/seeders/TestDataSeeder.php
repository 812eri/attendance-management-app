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

        $targetDate = Carbon::today();

        foreach ($usersData as $userData) {
            // --- ユーザー作成 ---
            // emailが同じ人がいなければ作成、いればその人を取得
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password123'),
                    'role_id' => User::ROLE_USER, // ★ここを追加（一般ユーザーとして作成）
                    'email_verified_at' => now(),
                ]
            );

            // --- 勤怠データ作成（既にその日のデータがあれば作らない） ---
            // 9:00〜18:00
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
                ]
            );

            // --- 休憩データ作成（勤怠データが新規作成された場合のみ追加など制御しても良いが、今回は簡易的に作成） ---
            // 休憩データがまだなければ作成
            if ($attendance->rests()->doesntExist()) {
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