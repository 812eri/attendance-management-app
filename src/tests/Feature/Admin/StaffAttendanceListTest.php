<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class StaffAttendanceListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_can_access_staff_attendance_list_page()
    {
        $admin = User::factory()->create(['role_id' => 2]);
        $staff = User::factory()->create(['role_id' => 1]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.staff', $staff->id));

        $response->assertStatus(200);
        $response->assertSee('勤怠一覧');
        $response->assertSee($staff->name);
    }

    public function test_general_user_cannot_access_staff_attendance_list_page()
    {
        $generalUser = User::factory()->create(['role_id' => 1]);
        $otherStaff = User::factory()->create(['role_id' => 1]);

        $response = $this->actingAs($generalUser)->get(route('admin.attendance.staff', $otherStaff->id));

        $response->assertStatus(403);
    }

    public function test_shows_monthly_attendance_and_navigation()
    {
        $admin = User::factory()->create(['role_id' => 2]);
        $staff = User::factory()->create();

        $thisMonth = Carbon::today();
        Attendance::create([
            'user_id' => $staff->id,
            'date' => $thisMonth,
            'start_time' => '09:00:00',
        ]);

        $lastMonth = Carbon::today()->subMonth();
        Attendance::create([
            'user_id' => $staff->id,
            'date' => $lastMonth,
            'start_time' => '08:00:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.staff', $staff->id));
        $response->assertStatus(200);
        $response->assertSee('09:00');
        $response->assertDontSee('08:00');

        $url = route('admin.attendance.staff', ['id' => $staff->id, 'month' => $lastMonth->format('Y-m')]);
        $response = $this->actingAs($admin)->get($url);

        $response->assertStatus(200);
        $response->assertSee('08:00');
        $response->assertSee($lastMonth->format('Y/m'));
    }

    public function test_detail_link_transitions_to_daily_detail()
    {
        $admin = User::factory()->create(['role_id' => 2]);
        $staff = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $staff->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.staff', $staff->id));

        $response->assertSee(route('admin.attendance.show', $attendance->id));
    }
}
