<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Carbon;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_clock_out_button_functions_correctly()
    {
        $user = User::factory()->create(['role_id' => 1]);

        $this->actingAs($user)->post('/attendance/clockin');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('退勤');

        $this->actingAs($user)->post('/attendance/clockout');

        $responseAfter = $this->actingAs($user)->get('/attendance');
        $responseAfter->assertSee('退勤済');
    }

    public function test_clock_out_time_is_displayed_in_list()
    {
        $now = Carbon::create(2025, 1, 24, 18, 0, 0);
        Carbon::setTestNow($now);

        $user = User::factory()->create(['role_id' => 1]);

        $this->actingAs($user)->post('/attendance/clockin');
        $this->actingAs($user)->post('/attendance/clockout');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee($now->format('H:i'));
    }

}
