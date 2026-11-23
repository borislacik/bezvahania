<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirects www.<APP_DOMAIN> to <APP_DOMAIN> permanently.
 */
class RedirectWww
{
    public function handle(Request $request, Closure $next): Response
    {
        $domain = (string) config('app.domain');

        if ($request->getHost() === 'www.'.$domain) {
            return redirect()->to(
                $request->getScheme().'://'.$domain.$request->getRequestUri(),
                Response::HTTP_MOVED_PERMANENTLY,
            );
        }

        return $next($request);
    }
}
