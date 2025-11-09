<?php

namespace Tests\Unit\Requests;

use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_ensure_is_not_rate_limited_throws_when_exceeded(): void
    {
        /** @var LoginRequest $request */
        $request = LoginRequest::create('/login', 'POST', ['email' => 'a@example.com', 'password' => 'secret']);
        $request->setContainer(app());
        $request->setRedirector(app('redirect'));
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $key = $request->throttleKey();
        // Simulate reaching the limit
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key);
        }

        $this->expectException(ValidationException::class);
        $request->ensureIsNotRateLimited();

        // Cleanup
        RateLimiter::clear($key);
    }
}
