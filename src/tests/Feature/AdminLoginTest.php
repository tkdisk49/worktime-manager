<?php

namespace Tests\Feature;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\Admin */
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function testAdminEmailIsRequiredOnLogin()
    {
        $response = $this->post(route('admin.login.store'), [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function testAdminPasswordIsRequiredOnLogin()
    {
        $response = $this->post(route('admin.login.store'), [
            'email' => $this->admin->email,
            'password' => '',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function testAdminLoginFailsWhenCredentialsDoNotMatchOnLogin()
    {
        $response = $this->post(route('admin.login.store'), [
            'email' => 'wrong@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
