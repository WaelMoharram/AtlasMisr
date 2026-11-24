<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/** Arabic is served from "/", English from "/en". The route group passes the locale code. */
class SetSiteLocale
{
    public function handle(Request $request, Closure $next, string $locale): Response
    {
        app()->setLocale($locale);
        View::share('lang', $locale);
        View::share('otherLang', $locale === 'ar' ? 'en' : 'ar');
        View::share('dir', $locale === 'ar' ? 'rtl' : 'ltr');

        return $next($request);
    }
}
