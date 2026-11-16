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

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for the Content-Security-Policy middleware.
 */
class ContentSecurityPolicyTest extends TestCase
{
    use RefreshDatabase;

    private const ENFORCE = 'Content-Security-Policy';

    private const REPORT_ONLY = 'Content-Security-Policy-Report-Only';

    /**
     * Enforces the policy regardless of the local environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        config(['custom.csp.report_only' => false]);
    }

    /**
     * Enforces the configured policy on web pages.
     */
    public function testEnforcesPolicyOnWebPages(): void
    {
        $response = $this->get('/login')->assertOk();

        $response->assertHeaderMissing(self::REPORT_ONLY);
        $policy = (string) $response->headers->get(self::ENFORCE);

        $this->assertStringContainsString("default-src 'self'", $policy);
        $this->assertStringContainsString("script-src 'self' https://cdnjs.cloudflare.com", $policy);
        $this->assertStringContainsString("frame-ancestors 'none'", $policy);
        $this->assertStringNotContainsString('unsafe-inline', $policy);
    }

    /**
     * Keeps the main page free of markup the policy would block.
     */
    public function testIndexHasNoInlineScriptsOrStyles(): void
    {
        $user = User::create([
            'username' => 'testuser',
            'password' => 'hashed',
            'fullname' => 'Test User',
            'timezone' => 'UTC',
        ]);

        $html = (string) $this->actingAs($user)->get('/')->assertOk()->getContent();

        $this->assertDoesNotMatchRegularExpression('/<script(?![^>]*\bsrc=)[^>]*>/i', $html);
        $this->assertDoesNotMatchRegularExpression('/<style\b/i', $html);
        $this->assertDoesNotMatchRegularExpression('/\son[a-z]+\s*=/i', $html);
        $this->assertDoesNotMatchRegularExpression('/\sstyle\s*=/i', $html);
        $this->assertStringContainsString('data-chart=', $html);
    }

    /**
     * Applies the policy to redirects from the web group too.
     */
    public function testPolicyOnRedirects(): void
    {
        $this->get('/')->assertRedirect()->assertHeader(self::ENFORCE);
    }

    /**
     * Sends the report-only header instead when configured.
     */
    public function testReportOnlyMode(): void
    {
        config(['custom.csp.report_only' => true]);

        $response = $this->get('/login')->assertOk();

        $response->assertHeaderMissing(self::ENFORCE);
        $response->assertHeader(self::REPORT_ONLY);
    }
}
