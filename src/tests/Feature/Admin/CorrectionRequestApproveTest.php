<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CorrectionRequestApproveTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_can_access_approve_screen()
    {
        $admin = User::factory()->create(['role_id' => 2]);
        $user = User::factory()->create();
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $request = StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'new_start_time' => '10:00:00',
            'new_end_time' => '19:00:00',
            'new_break_start' => '13:00:00',
            'new_break_end' => '14:00:00',
            'new_remarks' => '遅刻のため修正',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.stamp_correction_request.approve', $request->id));

        $response->assertStatus(200);
        $response->assertSee('承認');
        $response->assertSee('遅刻のため修正');
    }

    public function test_general_user_cannot_access_approve_screen()
    {
        $generalUser = User::factory()->create(['role_id' => 1]);
        $user = User::factory()->create();
        $attendance = Attendance::create(['user_id' => $user->id, 'date' => Carbon::today(), 'start_time' => '09:00:00']);

        $request = StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'new_start_time' => '09:00:00',
            'new_end_time' => '18:00:00',
            'new_break_start' => '12:00:00',
            'new_break_end' => '13:00:00',
            'new_remarks' => 'テスト',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($generalUser)->get(route('admin.stamp_correction_request.approve', $request->id));

        $response->assertStatus(403);
    }

    public function test_approve_process_updates_attendance_and_request_status()
    {
        $admin = User::factory()->create(['role_id' => 2]);
        $user = User::factory()->create();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'remarks' => '元々の備考',
        ]);

        $request = StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'new_start_time' => '10:00:00',
            'new_end_time' => '19:00:00',
            'new_break_start' => '13:00:00',
            'new_break_end' => '14:00:00',
            'new_remarks' => '変更後の備考',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.stamp_correction_request.approve.action', $request->id));

        $response->assertStatus(302);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'remarks' => '変更後の備考',
        ]);

        $updatedAttendance = Attendance::find($attendance->id);
        $this->assertStringContainsString('10:00:00', $updatedAttendance->start_time);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);
    }
}
