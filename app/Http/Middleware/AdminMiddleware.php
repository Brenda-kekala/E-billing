<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() || ! $request->user()->is_admin) {
            abort(403, 'You do not have access to this area.');
        }
        // if a user  is not logged in or not an admin  it shows forbidden page

        return $next($request);
    }
}