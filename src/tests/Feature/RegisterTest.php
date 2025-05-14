<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function testNameIsRequiredOnRegistration()
    {
        $response = $this->post(route('register'), [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['name' => 'お名前を入力してください']);
    }

    public function testEmailIsRequiredOnRegistration()
    {
        $response = $this->post(route('register'), [
            'name' => 'test',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['email' => 'メールアドレスを入力してください']);
    }

    public function testPasswordIsTooShortOnRegistration()
    {
        $response = $this->post(route('register'), [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'pass',
            'password_confirmation' => 'pass',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
    }

    public function testPasswordConfirmationMustMatchPasswordOnRegistration()
    {
        $response = $this->post(route('register'), [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'wrongpass',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }

    public function testPasswordIsRequiredOnRegistration()
    {
        $response = $this->post(route('register'), [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors(['password' => 'パスワードを入力してください']);
    }

    public function testSuccessfulRegistrationPersistsUserRecord()
    {
        $response = $this->post(route('register'), [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'test',
            'email' => 'test@example.com',
        ]);
    }
}
