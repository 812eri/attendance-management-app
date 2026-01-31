<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\rest;
use App\Models\Attendance;
use Illuminate\support\Carbon;

class DetailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_detail_screen_shows_login_user_name()
    {
        $user = User::factory()->create(['role_id' => 1, 'name' => 'テスト太郎']);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);
        $response = $this->get(route('attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee($user->name);
    }

    public function test_detail_screen_shows_selected_date()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $date = '2026-01-25';

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $date,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);
        $response = $this->get(route('attendance.show', $attendance->id));

        $response->assertSee('2026');
        $response->assertSee('1');
        $response->assertSee('25');
    }

    public function test_detail_screen_shows_correct_clock_in_and_out_time()
    {
        $user = User::factory()->create(['role_id' => 1]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '08:30:00',
            'end_time' => '17:45:00',
        ]);

        $this->actingAs($user);
        $response = $this->get(route('attendance.show', $attendance->id));

        $response->assertSee('08:30');
        $response->assertSee('17:45');
    }

    public function test_detail_screen_shows_correct_break_time()
    {
        $user = User::factory()->create(['role_id' => 1]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        Rest::create([
            'attendance_id' => $attendance->id,
            'start_time' => '12:00:00',
            'end_time'   => '13:00:00',
        ]);

        $this->actingAs($user);
        $response = $this->get(route('attendance.show', $attendance->id));

        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
