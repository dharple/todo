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

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

/**
 * Application service provider.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login', $this->limitLogins(...));
    }

    /**
     * Returns the limits applied to login attempts.
     *
     * @param Request $request The current HTTP request.
     *
     * @return Limit[]
     */
    private function limitLogins(Request $request): array
    {
        $input = $request->input('username');
        $username = Str::transliterate(Str::lower(is_string($input) ? $input : ''));
        $ip = (string) $request->ip();

        $limits = [
            Limit::perSecond(...config('custom.throttling.login.ip'))->by('ip:' . $ip),
            Limit::perSecond(...config('custom.throttling.login.username'))->by('username:' . $username),
        ];

        return array_map(fn (Limit $limit) => $limit->response($this->respondToThrottledLogin(...)), $limits);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Returns to the login form with an error when attempts are throttled.
     *
     * @param Request                   $request The current HTTP request.
     * @param array<string, int|string> $headers The rate limit headers.
     *
     * @return RedirectResponse
     */
    private function respondToThrottledLogin(Request $request, array $headers): RedirectResponse
    {
        Log::warning('Login attempts throttled', ['ip' => $request->ip()]);

        $seconds = (int) ($headers['Retry-After'] ?? 60);
        $username = $request->input('username');

        return redirect()->route('login')
            ->with('controller_errors', ['Too many login attempts. Please try again in ' . $seconds . ' seconds.'])
            ->with('_old_input.username', is_string($username) ? $username : '');
    }
}
