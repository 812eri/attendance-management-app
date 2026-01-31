<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Carbon;

class DetailCorrectionTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_validation_error_when_clock_in_is_after_clock_out()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $data = [
            'attendance_id' => $attendance->id,
            'new_start_time' => '19:00',
            'new_end_time' => '18:00',
            'new_break_start' => '12:00',
            'new_break_end' => '13:00',
            'new_remarks' => '修正テスト',
        ];
        $response = $this->post(route('stamp_correction_request.store'), $data);

        $response->assertSessionHasErrors();
    }

    public function test_validation_error_when_break_time_is_after_clock_out()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $data = [
            'attendance_id' => $attendance->id,
            'new_start_time' => '09:00',
            'new_end_time' => '18:00',
            'new_break_start' => '19:00',
            'new_break_end' => '20:00',
            'new_remarks' => '修正テスト',
        ];

        $response = $this->post(route('stamp_correction_request.store'), $data);
        $response->assertSessionHasErrors();
    }

    public function test_validation_error_when_remark_is_empty()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $data =[
            'attendance_id' => $attendance->id,
            'new_start_time' => '09:00',
            'new_end_time' => '18:00',
            'new_break_start' => '12:00',
            'new_break_end' => '13:00',
            'new_remarks' => '',
        ];

        $response = $this->post(route('stamp_correction_request.store'), $data);

        $response->assertSessionHasErrors('new_remarks');
    }

    public function test_correction_request_is_created_successfully()
    {
        $user = User::factory()->create(['role_id' => 1]);
        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        $this->actingAs($user);

        $validData = [
            'attendance_id' => $attendance->id,
            'new_start_time' => '10:00',
            'new_end_time' => '19:00',
            'new_break_start' => '12:00',
            'new_break_end' => '13:00',
            'new_remarks' => '電車遅延のため',
        ];

        $response = $this->post(route('stamp_correction_request.store'), $validData);

        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertDatabaseHas('stamp_correction_requests', [
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'new_start_time' => '10:00:00',
            'new_remarks' => '電車遅延のため',
        ]);
    }

    public function test_shows_pending_requests_in_list()
    {
        $user = User::factory()->create(['role_id' => 1]);

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => Carbon::today(),
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
        ]);

        StampCorrectionRequest::create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'new_start_time' => '10:00:00',
            'new_end_time' => '19:00:00',
            'new_break_start' => '12:00:00',
            'new_break_end' => '13:00:00',
            'new_remarks' => 'テスト申請',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get(route('stamp_correction_request.index'));

        $response->assertStatus(200);
        $response->assertSee('承認待ち');
        $response->assertSee('テスト申請');
    }
}
