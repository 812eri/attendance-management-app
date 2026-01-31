<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffListTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_can_access_staff_list_page()
    {
        $admin = User::factory()->create(['role_id' => 2]);

        $response = $this->actingAs($admin)->get(route('admin.staff.list'));

        $response->assertStatus(200);
        $response->assertSee('スタッフ一覧');
    }

    public function test_general_user_cannot_access_staff_list_page()
    {
        $generalUser = User::factory()->create(['role_id' => 1]);

        $response = $this->actingAs($generalUser)->get(route('admin.staff.list'));

        $response->assertStatus(403);
    }

    public function test_shows_registered_staff_list()
    {
        $admin = User::factory()->create(['role_id' => 2]);

        $staff1 = User::factory()->create(['name' => '田中太郎', 'email' => 'tanaka@example.com', 'role_id' => 1]);
        $staff2 = User::factory()->create(['name' => '佐藤花子', 'email' => 'sato@example.com', 'role_id' => 1]);
        $staff3 = User::factory()->create(['name' => '鈴木一郎', 'email' => 'suzuki@example.com', 'role_id' => 1]);

        $response = $this->actingAs($admin)->get(route('admin.staff.list'));

        $response->assertStatus(200);

        $response->assertSee('田中太郎');
        $response->assertSee('tanaka@example.com');
        $response->assertSee('佐藤花子');
        $response->assertSee('鈴木一郎');

        $response->assertSee(route('admin.attendance.staff', $staff1->id));
    }
}
