<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Tests\Feature\Http;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Tests for the login rate limiter.
 */
class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    private const THROTTLED = 'Too many login attempts';

    /**
     * Sends a login attempt with a wrong password from the given IP.
     *
     * @param string                $username   The username to log in as.
     * @param string                $remoteAddr The connecting IP.
     * @param array<string, string> $headers    Extra request headers.
     *
     * @return TestResponse
     */
    private function attempt(string $username, string $remoteAddr = '203.0.113.10', array $headers = []): TestResponse
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $remoteAddr])
            ->post('/login', ['username' => $username, 'password' => 'wrong'], $headers);
    }

    /**
     * Asserts that the response was handled as a normal failed login.
     *
     * @param TestResponse $response The login response.
     */
    private function assertNotThrottled(TestResponse $response): void
    {
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('controller_errors', ['Invalid credentials.']);
    }

    /**
     * Asserts that the response redirected with the throttled message.
     *
     * @param TestResponse $response The login response.
     */
    private function assertThrottled(TestResponse $response): void
    {
        $response->assertRedirect(route('login'));
        $this->followRedirects($response)->assertOk()->assertSee(self::THROTTLED);
    }

    /**
     * Clears the Laravel Cloud marker set by some tests.
     */
    protected function tearDown(): void
    {
        unset($_SERVER['LARAVEL_CLOUD']);

        parent::tearDown();
    }

    /**
     * Doesn't let a forged X-Forwarded-For evade the limit on Laravel Cloud.
     */
    public function testForgedForwardedForDoesNotEvadeLimitOnCloud(): void
    {
        $_SERVER['LARAVEL_CLOUD'] = '1';

        for ($i = 1; $i <= config('custom.throttling.login.ip.maxAttempts'); $i++) {
            $this->assertNotThrottled($this->attempt('user' . $i, '10.0.0.1', [
                'CF-Connecting-IP' => '198.51.100.7',
                'X-Forwarded-For'  => '192.0.2.' . $i,
            ]));
        }

        $this->assertThrottled($this->attempt('another', '10.0.0.1', [
            'CF-Connecting-IP' => '198.51.100.7',
            'X-Forwarded-For'  => '192.0.2.99',
        ]));
    }

    /**
     * Rejects an array in place of the username without an error.
     */
    public function testNonStringUsernameIsHandled(): void
    {
        $this->post('/login', ['username' => ['someone'], 'password' => 'wrong'])
            ->assertRedirect(route('login'));
    }

    /**
     * Limits one IP across many usernames.
     */
    public function testThrottlesIpAcrossUsernames(): void
    {
        for ($i = 1; $i <= config('custom.throttling.login.ip.maxAttempts'); $i++) {
            $this->assertNotThrottled($this->attempt('user' . $i));
        }

        $this->assertThrottled($this->attempt('another'));
    }

    /**
     * Limits one username across any number of IPs.
     */
    public function testThrottlesUsernameAcrossIps(): void
    {
        for ($i = 1; $i <= config('custom.throttling.login.username.maxAttempts'); $i++) {
            $this->assertNotThrottled($this->attempt('someone', '203.0.113.' . $i));
        }

        $this->assertThrottled($this->attempt('someone', '203.0.113.200'));
    }

    /**
     * Limits one username across any number of IPs, regardless of case.
     */
    public function testThrottlesUsernameIgnoresCase(): void
    {
        for ($i = 1; $i <= config('custom.throttling.login.username.maxAttempts'); $i++) {
            $this->assertNotThrottled($this->attempt('someone', '203.0.113.' . $i));
        }

        $this->assertThrottled($this->attempt('SOMEONE', '203.0.113.200'));
    }
}
