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

use Illuminate\Http\Middleware\TrustProxies as BaseTrustProxies;
use Illuminate\Http\Request;

/**
 * Resolves the visitor's IP from a proxy-set header, when one is available.
 *
 * When every proxy is trusted, Symfony reports the leftmost X-Forwarded-For
 * entry as the client IP.  Cloudflare preserves client-supplied entries there,
 * so that value can be forged.
 */
class TrustProxies extends BaseTrustProxies
{
    private const LARAVEL_CLOUD_CLIENT_IP_HEADER = 'CF-Connecting-IP';

    /**
     * Returns the header holding the visitor's IP, if one applies.
     *
     * Laravel Cloud is checked per request, rather than in the config file, so
     * the result doesn't depend on the environment `config:cache` ran in.
     */
    private function getClientIpHeader(): ?string
    {
        $default = laravel_cloud() ? self::LARAVEL_CLOUD_CLIENT_IP_HEADER : null;

        return config('trustedproxy.client_ip_header') ?? $default;
    }

    /**
     * Sets the trusted proxies, then pins X-Forwarded-For to the visitor's IP.
     */
    protected function setTrustedProxyIpAddresses(Request $request): void
    {
        parent::setTrustedProxyIpAddresses($request);

        $header = $this->getClientIpHeader();

        if ($header === null || !$request->isFromTrustedProxy()) {
            return;
        }

        $clientIp = $request->headers->get($header);

        if (filter_var($clientIp, FILTER_VALIDATE_IP) !== false) {
            $request->headers->set('X-Forwarded-For', $clientIp);
        }
    }
}
