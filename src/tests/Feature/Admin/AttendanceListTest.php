<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_can_access_attendance_list_page()
    {
        $admin = User::factory()->create(['role_id' => 2]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee('勤怠一覧');
    }

    public function test_general_user_cannot_access_admin_attendance_list_page()
    {
        $user = User::factory()->create(['role_id' => 1]);

        $response = $this->actingAs($user)->get('/admin/attendance/list');

        $response->assertStatus(403);
    }

    public function test_shows_attendance_records_for_the_date()
    {
        $admin = User::factory()->create(['role_id' => 2]);
        $user = User::factory()->create(['name' => 'テスト花子']);

        Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    public function test_shows_previous_day_data()
    {
        $admin = User::factory()->create(['role_id' => 2]);
        $user = User::factory()->create(['name' => '過去の人']);

        $yesterday = Carbon::yesterday();
        Attendance::create([
            'user_id' => $user->id,
            'date' => $yesterday,
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
        ]);

        $url = '/admin/attendance/list?date=' . $yesterday->format('Y-m-d');

        $response = $this->actingAs($admin)->get($url);

        $response->assertStatus(200);
        $response->assertSee('08:00');
        $response->assertSee($yesterday->format('Y/m/d'));
    }

    public function test_detail_link_is_correct()
    {
        $admin = User::factory()->create(['role_id' => 2]);
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
        ]);

        $response = $this->actingAs($admin)->get('/admin/attendance/list');

        $response->assertSee("admin/attendance/{$attendance->id}");
    }
}
