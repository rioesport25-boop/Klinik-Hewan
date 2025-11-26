<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrustNgrokProxy
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Trust ngrok and cloudflare tunnel proxy headers
        if ($request->hasHeader('X-Original-Host')) {
            $request->headers->set('X-Forwarded-Host', $request->header('X-Original-Host'));
        }

        // Set proper scheme for HTTPS (ngrok or cloudflare)
        if (
            $request->header('X-Forwarded-Proto') === 'https'
            || str_contains($request->header('Host', ''), 'ngrok')
            || str_contains($request->header('Host', ''), 'trycloudflare.com')
        ) {
            $request->server->set('HTTPS', 'on');
            $_SERVER['HTTPS'] = 'on';
        }

        // Trust all proxies for Cloudflare Tunnel
        if (str_contains($request->header('Host', ''), 'trycloudflare.com')) {
            $request->setTrustedProxies(['*'], Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_HOST | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);
        }

        return $next($request);
    }
}
