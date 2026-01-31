<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_login_screen_can_be_rendered()
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
        $response->assertSee('ログイン');
    }

    public function test_admin_users_can_authenticate_using_the_login_screen()
    {
        $admin = User::factory()->create([
            'role_id' => 2,
            'password' => bcrypt($password = 'password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => $password,
        ]);

        $this->assertAuthenticatedAs($admin);

        $response->assertRedirect('/admin/attendance/list');
    }

    public function test_admin_users_can_not_authenticate_with_invalid_password()
    {
        $admin = User::factory()->create([
            'role_id' => 2,
        ]);

        $this->post('/admin/login', [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_general_users_cannot_login_from_admin_login_screen()
    {
        $user = User::factory()->create([
            'role_id' => 1,
            'password' => bcrypt($password = 'password123'),
        ]);

        $response = $this->post('/admin/login', [
            'email' => $user->email,
            'password' => $password,
        ]);

        $this->assertGuest();
    }
}
