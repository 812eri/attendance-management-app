<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;

class StartTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_attendance_button_functions_correctly()
    {
        $user = User::factory()->create([
            'role_id' => 1,
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);
        $response->assertSee('出勤');

        $postResponse = $this->actingAs($user)->post('/attendance/clockin');

        $responseAfter = $this->actingAs($user)->get('/attendance');
        $responseAfter->assertSee('出勤中');
    }

    public function test_attendance_button_is_hidden_for_clocked_out_user()
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 24, 12, 0, 0));

        $user = User::factory()->create(['role_id' => 1]);

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertDontSee('出勤');
    }

    public function test_attendance_time_is_displayed_in_list()
    {
        $now = Carbon::create(2025, 1, 24, 9, 0, 0);
        Carbon::setTestNow($now);

        $user = User::factory()->create(['role_id' => 1]);

        $this->actingAs($user)->post('/attendance/clockin');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee($now->format('H:i'));
    }
}
