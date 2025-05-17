<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceTimestampTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Carbon $now;

    protected function setUp(): void
    {
        parent::setUp();

        $this->now = Carbon::create(2025, 5, 10, 8, 0, 0, 'Asia/Tokyo');
        Carbon::setTestNow($this->now);

        $this->user = User::factory()->create();
    }

    public function testCreateDisplaysCurrentDateAndTime()
    {
        $response = $this->actingAs($this->user, 'web')
            ->get(route('attendance.create'));

        $response->assertStatus(200);

        $expectedDate = $this->now->isoFormat('YYYY年M月D日(ddd)');
        $expectedTime = $this->now->format('H:i');

        $response->assertSeeText($expectedDate);
        $response->assertSeeText($expectedTime);
    }
}
