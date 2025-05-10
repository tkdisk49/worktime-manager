<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmployeeLoginTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'employee@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function testEmployeeEmailIsRequiredOnLogin()
    {
        $response = $this->post(route('login.store'), [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function testEmployeePasswordIsRequiredOnLogin()
    {
        $response = $this->post(route('login.store'), [
            'email' => $this->user->email,
            'password' => '',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function testEmployeeLoginFailsWhenCredentialsDoNotMatchOnLogin()
    {
        $response = $this->post(route('login.store'), [
            'email' => 'wrong@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
    }
}
