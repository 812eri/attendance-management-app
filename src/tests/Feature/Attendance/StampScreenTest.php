<?php

namespace Tests\Feature\Attendance;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class StampScreenTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_current_date_and_time_are_displayed()
    {
        $user = User::factory()->create([
            'role_id' => 1,
        ]);

        $now = Carbon::create(2026, 1, 1, 12, 0, 0);
        Carbon::setTestNow($now);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        $response->assertSee($now->format('Y年m月d日'));
        $response->assertSee($now->format('H:i'));
    }
}
