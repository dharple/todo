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

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adds the configured Content-Security-Policy header to web responses.
 */
class ContentSecurityPolicy
{
    /**
     * Joins directive names and their sources into a policy string.
     *
     * @param array<string, list<string>> $directives Directive names mapped to their sources.
     *
     * @return string
     */
    private function buildPolicy(array $directives): string
    {
        $parts = [];
        foreach ($directives as $name => $sources) {
            $parts[] = trim($name . ' ' . implode(' ', $sources));
        }

        return implode('; ', $parts);
    }

    /**
     * Sets the policy header, or its report-only variant, on the response.
     *
     * @param Request $request The incoming request.
     * @param Closure $next    The next handler in the pipeline.
     *
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $policy = $this->buildPolicy((array) config('custom.csp.directives', []));
        if ($policy === '') {
            return $response;
        }

        $header = 'Content-Security-Policy';
        if (config('custom.csp.report_only')) {
            $header .= '-Report-Only';
        }

        $response->headers->set($header, $policy);

        return $response;
    }
}
