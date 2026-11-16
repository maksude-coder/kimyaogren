<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * The student interface is Turkish; the admin panel keeps the app locale.
 */
class UseStudentLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale('tr');

        return $next($request);
    }
}
