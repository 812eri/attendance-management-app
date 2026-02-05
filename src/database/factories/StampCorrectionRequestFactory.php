<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\StampCorrectionRequest;

class StampCorrectionRequestFactory extends Factory
{
    protected $model = StampCorrectionRequest::class;
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'attendance_id' => \App\Models\Attendance::factory(),
            'new_start_time' => '09:00:00',
            'new_end_time' => '18:00:00',
            'new_remarks' => '修正申請テスト',
            'status' => 'pending',
        ];
    }
}
