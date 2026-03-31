<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Auth\Middleware\RequirePassword;

class EnsurePasswordIsConfirmed extends RequirePassword
{
    /**
     * LanCore SSO users have no local password and can never complete the
     * password confirmation flow, so we let them through immediately.
     */
    public function handle($request, Closure $next, $redirectToRoute = null, $passwordTimeoutSeconds = null): mixed
    {
        if ($request->user() instanceof User && $request->user()->isLanCoreUser()) {
            return $next($request);
        }

        return parent::handle($request, $next, $redirectToRoute, $passwordTimeoutSeconds);
    }
}
