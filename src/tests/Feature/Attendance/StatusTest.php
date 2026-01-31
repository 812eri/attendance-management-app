<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class StatusTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_status_is_off_duty_when_no_attendance()
    {
        $user = User::factory()->create(['role_id' => 1]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    public function test_status_is_working_when_clocked_in()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $now = Carbon::now();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $now->format('Y-m-d'),
            'start_time' => $now->copy()->subHour(),
            'end_time' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    public function test_status_is_on_break_when_resting()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $now = Carbon::now();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $now->format('Y-m-d'),
            'start_time' => $now->copy()->subHours(2),
            'end_time' => null,
        ]);

        Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => $now->copy()->subMinutes(30),
            'end_time' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    public function test_status_is_left_work_when_clocked_out()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $now = Carbon::now();

        Attendance::create([
            'user_id' => $user->id,
            'date' => $now->format('Y-m-d'),
            'start_time' => $now->copy()->subHours(9),
            'end_time' => $now->copy()->subHour(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }
}
