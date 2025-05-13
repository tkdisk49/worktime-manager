<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    public function testEmailVerificationNotificationIsSent()
    {
        $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->first();

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    public function testEmailVerificationRedirectWhenButtonClicked()
    {
        $this->user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($this->user, 'web');

        $response = $this->get(route('verification.notice'));
        $response->assertStatus(200)
            ->assertSee('認証はこちらから');

        $response = $this->get(route('mailhog.redirect'));
        $response->assertRedirect('http://localhost:8025');
    }

    public function testNavigatesToAttendanceRegistrationAfterEmailVerification()
    {
        $this->user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($this->user, 'web');

        $url = URL::signedRoute('verification.verify', [
            'id' => $this->user->id,
            'hash' => sha1($this->user->email),
        ]);

        $response = $this->followingRedirects()->get($url);
        $response->assertStatus(200)
            ->assertSeeText('勤怠登録')
            ->assertSeeText('出勤');
    }
}
