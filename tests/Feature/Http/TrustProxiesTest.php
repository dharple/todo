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

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Tests for client IP resolution behind proxies.
 */
class TrustProxiesTest extends TestCase
{
    private const PROXY = '10.0.0.1';

    private const VISITOR = '198.51.100.7';

    /**
     * Returns scenarios as: proxies, IP header, Cloud, remote address, headers, expected IP.
     *
     * @return array<string, array{?string, ?string, bool, string, array<string, string>, string}>
     */
    public static function clientIpProvider(): array
    {
        $forwarded = ['X-Forwarded-For' => self::VISITOR];
        $forged = ['X-Forwarded-For' => '192.0.2.1, ' . self::VISITOR];
        $cloudflare = ['CF-Connecting-IP' => self::VISITOR, 'X-Forwarded-For' => '192.0.2.1, ' . self::VISITOR];

        return [
            'no proxy configured ignores headers'     => [null, null, false, self::PROXY, $cloudflare, self::PROXY],
            'listed proxy uses forwarded for'         => ['10.0.0.0/8', null, false, self::PROXY, $forwarded, self::VISITOR],
            'listed proxy ignores forged entries'     => ['10.0.0.0/8', null, false, self::PROXY, $forged, self::VISITOR],
            'unlisted proxy is not believed'          => ['10.0.0.0/8', null, false, '203.0.113.5', $forwarded, '203.0.113.5'],
            'cloud prefers cloudflare header'         => [null, null, true, self::PROXY, $cloudflare, self::VISITOR],
            'cloud falls back to forwarded for'       => [null, null, true, self::PROXY, $forwarded, self::VISITOR],
            'cloud ignores invalid cloudflare header' => [null, null, true, self::PROXY, ['CF-Connecting-IP' => 'junk'] + $forwarded, self::VISITOR],
            'configured header is used'               => ['*', 'X-Real-IP', false, self::PROXY, ['X-Real-IP' => self::VISITOR] + $forged, self::VISITOR],
            'header ignored from untrusted host'      => ['10.0.0.0/8', 'X-Real-IP', false, '203.0.113.5', ['X-Real-IP' => self::VISITOR], '203.0.113.5'],
        ];
    }

    /**
     * Registers a route that reports the resolved client IP.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/test-client-ip', fn (Request $request) => $request->ip());
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
     * Resolves the client IP according to the proxy configuration.
     *
     * @param ?string               $proxies
     * @param ?string               $header
     * @param bool                  $cloud
     * @param string                $remoteAddr
     * @param array<string, string> $headers
     * @param string                $expected
     */
    #[DataProvider('clientIpProvider')]
    public function testResolvesClientIp(?string $proxies, ?string $header, bool $cloud, string $remoteAddr, array $headers, string $expected): void
    {
        config(['trustedproxy.proxies' => $proxies, 'trustedproxy.client_ip_header' => $header]);

        if ($cloud) {
            $_SERVER['LARAVEL_CLOUD'] = '1';
        }

        $this->withServerVariables(['REMOTE_ADDR' => $remoteAddr])
            ->get('/test-client-ip', $headers)
            ->assertOk()
            ->assertSee($expected, false);
    }
}
