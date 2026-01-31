<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CorrectionRequestListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_can_access_correction_request_list()
    {
        $admin = User::factory()->create(['role_id' => 2]);

        $response = $this->actingAs($admin)->get(route('admin.stamp_correction_request.index'));

        $response->assertStatus(200);
        $response->assertSee('申請一覧');
    }

    public function test_general_user_cannot_access_admin_correction_request_list()
    {
        $user = User::factory()->create(['role_id' => 1]);

        $response = $this->actingAs($user)->get(route('admin.stamp_correction_request.index'));

        $response->assertStatus(403);
    }

    public function test_shows_all_pending_requests()
    {
        $admin = User::factory()->create(['role_id' => 2]);

        $userA = User::factory()->create(['name' => 'ユーザーA']);
        $attendanceA = Attendance::create(['user_id' => $userA->id, 'date' => Carbon::today(), 'start_time' => '09:00:00']);
        StampCorrectionRequest::create([
            'user_id' => $userA->id,
            'attendance_id' => $attendanceA->id,
            'new_start_time' => '09:00:00',
            'new_end_time' => '18:00:00',
            'new_break_start' => '12:00:00',
            'new_break_end' => '13:00:00',
            'new_remarks' => '申請A',
            'status' => 'pending'
        ]);

        $userB = User::factory()->create(['name' => 'ユーザーB']);
        $attendanceB = Attendance::create(['user_id' => $userB->id, 'date' => Carbon::today(), 'start_time' => '10:00:00']);
        StampCorrectionRequest::create([
            'user_id' => $userB->id,
            'attendance_id' => $attendanceB->id,
            'new_start_time' => '10:00:00',
            'new_end_time' => '19:00:00',
            'new_break_start' => '12:00:00',
            'new_break_end' => '13:00:00',
            'new_remarks' => '申請B',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($admin)->get(route('admin.stamp_correction_request.index', ['tab' => 'pending']));

        $response->assertStatus(200);
        $response->assertSee('ユーザーA');
        $response->assertSee('ユーザーB');
        $response->assertSee('申請A');
        $response->assertSee('申請B');
    }

    public function test_shows_all_approved_requests()
    {
        $admin = User::factory()->create(['role_id' => 2]);

        $userC = User::factory()->create(['name' => 'ユーザーC']);
        $attendanceC = Attendance::create(['user_id' => $userC->id, 'date' => Carbon::today(), 'start_time' => '09:00:00']);
        StampCorrectionRequest::create([
            'user_id' => $userC->id,
            'attendance_id' => $attendanceC->id,
            'new_start_time' => '09:00:00',
            'new_end_time' => '18:00:00',
            'new_break_start' => '12:00:00',
            'new_break_end' => '13:00:00',
            'new_remarks' => '申請C',
            'status' => 'approved'
        ]);

        $userD = User::factory()->create(['name' => 'ユーザーD']);
        $attendanceD = Attendance::create(['user_id' => $userD->id, 'date' => Carbon::today(), 'start_time' => '10:00:00']);
        StampCorrectionRequest::create([
            'user_id' => $userD->id,
            'attendance_id' => $attendanceD->id,
            'new_start_time' => '10:00:00',
            'new_end_time' => '19:00:00',
            'new_break_start' => '12:00:00',
            'new_break_end' => '13:00:00',
            'new_remarks' => '申請D',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($admin)->get(route('admin.stamp_correction_request.index', ['tab' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('ユーザーC');
        $response->assertDontSee('ユーザーD');
    }

    public function test_detail_link_transitions_to_approve_screen()
    {
        $admin = User::factory()->create(['role_id' => 2]);
        $user = User::factory()->create();
        $attendance = Attendance::create(['user_id' => $user->id, 'date' => Carbon::today(), 'start_time' => '09:00:00']);

        $request = StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'new_start_time' => '09:00:00',
            'new_end_time' => '18:00:00',
            'new_break_start' => '12:00:00',
            'new_break_end' => '13:00:00',
            'new_remarks' => 'リンク確認用',
            'status' => 'pending'
        ]);

        $response = $this->actingAs($admin)->get(route('admin.stamp_correction_request.index'));

        $response->assertSee(route('admin.stamp_correction_request.approve', $request->id));
    }
}

