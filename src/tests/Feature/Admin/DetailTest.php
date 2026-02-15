<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DetailTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_can_access_attendance_detail_page()
    {
        $admin = User::factory()->create(['role_id' => 2]);
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.attendance.show', $attendance->id));

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
        $response->assertSee($user->name);
    }

    public function test_general_user_cannot_access_admin_attendance_detail_page()
    {
        $generalUser = User::factory()->create(['role_id' => 1]);
        $attendance = Attendance::create([
            'user_id' => $generalUser->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
        ]);

        $response = $this->actingAs($generalUser)->get(route('admin.attendance.show', $attendance->id));

        $response->assertStatus(403);
    }

    public function test_admin_can_update_attendance()
    {
        $admin = User::factory()->create(['role_id' => 2]);
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'break_start' => '12:00:00',
            'break_end' => '13:00:00',
            'remarks' => '元々の備考',
        ]);

        $updateData =[
            'start_time' => '10:00',
            'end_time' => '19:00',
            'break_start' => '13:00',
            'break_end' => '14:00',
            'remarks' => '管理者による修正',
        ];

        $response = $this->actingAs($admin)->post(route('admin.attendance.update', $attendance->id), $updateData);

        $response->assertStatus(302);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'attendance_id' => $attendance->id,
            'new_start_time' => '10:00:00',
            'new_remarks' => '管理者による修正',
            'status' => 'pending',
        ]);
    }

    public function test_validation_error_when_start_time_is_after_end_time()
    {
        $admin = User::factory()->create(['role_id' => 2]);
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $invalidData =[
            'start_time' => '19:00',
            'end_time' => '18:00',
            'break_start' => '12:00',
            'break_end' => '13:00',
            'remarks' => 'テスト',
        ];

        $response = $this->actingAs($admin)->post(route('admin.attendance.update', $attendance->id), $invalidData);

        $response->assertSessionHasErrors();
    }
}
