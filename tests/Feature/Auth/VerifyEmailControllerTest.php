<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VerifyEmailControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_verifies_email_with_valid_signed_url(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify', now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        Event::fake([Verified::class]);

        $this->actingAs($user)
            ->get($url)
            ->assertRedirect('/dashboard?verified=1');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        Event::assertDispatched(Verified::class);
    }

    public function test_already_verified_user_is_redirected_without_changes(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $url = URL::temporarySignedRoute(
            'verification.verify', now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)
            ->get($url)
            ->assertRedirect('/dashboard?verified=1');

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_invalid_signature_results_in_403(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify', now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('tampered@example.com')]
        );

        $this->actingAs($user)
            ->get($url)
            ->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify', now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->get($url)
            ->assertRedirect(route('login'));
    }
}
