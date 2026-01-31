<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Carbon;

class BreakTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_break_start_button_functions_correctly()
    {
        $user = User::factory()->create(['role_id' => 1]);

        $this->actingAs($user)->post('/attendance/clockin');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入');

        $this->actingAs($user)->post('/attendance/break/start');

        $responseAfter = $this->actingAs($user)->get('/attendance');
        $responseAfter->assertSee('休憩中');
    }

    public function test_break_can_be_taken_multiple_times()
    {
        $user = User::factory()->create(['role_id' => 1]);

        $this->actingAs($user)->post('/attendance/clockin');
        $this->actingAs($user)->post('/attendance/break/start');
        $this->actingAs($user)->post('/attendance/break/end');

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertSee('休憩入');
    }

    public function test_break_end_button_functions_correctly()
    {
        $user = User::factory()->create(['role_id' => 1]);

        $this->actingAs($user)->post('/attendance/clockin');
        $this->actingAs($user)->post('/attendance/break/start');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');

        $this->actingAs($user)->post('/attendance/break/end');

        $responseAfter = $this->actingAs($user)->get('/attendance');
        $responseAfter->assertSee('出勤中');
    }

    public function test_break_end_can_be_done_multiple_times()
    {
        $user = User::factory()->create(['role_id' => 1]);

        $this->actingAs($user)->post('/attendance/clockin');
        $this->actingAs($user)->post('/attendance/break/start');
        $this->actingAs($user)->post('/attendance/break/end');
        $this->actingAs($user)->post('/attendance/break/start');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩戻');
    }

    public function test_break_time_is_displayed_in_list()
    {
        Carbon::settestNow(Carbon::create(2025, 1, 24, 12, 0, 0));

        $user = User::factory()->create(['role_id' => 1]);

        $this->actingAs($user)->post('/attendance/clockin');
        $this->actingAs($user)->post('/attendance/break/start');

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertSee('12:00');
    }
}
