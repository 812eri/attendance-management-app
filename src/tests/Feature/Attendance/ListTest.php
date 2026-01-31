<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Support\Carbon;

class ListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_shows_all_attendance_records_for_login_user()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $otherUser = User::factory()->create(['role_id' => 1]);

        $myAttendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '10:00:00',
            'end_time' => '19:00:00',
        ]);

        $otherAttendance = Attendance::create([
            'user_id' => $otherUser->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);
        $response = $this->get(route('attendance.list'));

        $response->assertStatus(200);

        $startTime = Carbon::parse($myAttendance->start_time)->format('H:i');
        $response->assertSee($startTime);

        $response->assertDontSee($otherUser->name);
    }

    public function test_shows_current_month_by_default()
    {
        Carbon::setTestNow('2026-01-24');
        $user = User::factory()->create(['role_id' => 1]);

        $response = $this->actingAs($user)->get(route('attendance.list'));

        $response->assertSee(Carbon::now()->format('Y/m'));
    }

    public function test_shows_previous_month_data()
    {
        $user = USer::factory()->create(['role_id' => 1]);

        $prevMonthDate = Carbon::now()->subMonth();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $prevMonthDate,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);

        $this->actingAs($user);
        $response = $this->get(route('attendance.list'));
        $response->assertSee('前月');

        $url = route('attendance.list', ['month' => $prevMonthDate->format('Y-m')]);
        $response = $this->get($url);

        $response->assertSee('08:00');
    }

    public function test_shows_next_month_data()
    {
        $user = User::factory()->create(['role_id' => 1]);

        $nextMonthDate = Carbon::now()->addMonth();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $nextMonthDate,
            'start_time' => '11:00:00',
            'end_time' => '20:00:00',
        ]);

        $this->actingAs($user);
        $response = $this->get(route('attendance.list'));
        $response->assertSee('翌月');

        $url = route('attendance.list', ['month' => $nextMonthDate->format('Y-m')]);
        $response = $this->get($url);

        $response->assertSee('11:00');
    }

    public function test_detail_button_transitions_correctly()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);
        $response = $this->get(route('attendance.list'));

        $response->assertSee('詳細');

        $targetUrl = route('attendance.show', $attendance->id);
        $response->assertSee($targetUrl, false);

        $detailResponse = $this->get($targetUrl);
        $detailResponse->assertStatus(200);
    }
}
