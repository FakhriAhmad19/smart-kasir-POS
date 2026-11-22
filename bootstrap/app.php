<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Cloudflared terminates HTTPS before forwarding to the local Laravel
        // server. Trust the local connector so asset() / @vite() preserve
        // the public HTTPS scheme instead of emitting mixed-content HTTP URLs.
        $middleware->trustProxies(at: ['127.0.0.1', '::1']);

        // Register custom route middleware aliases used by the application.
        $middleware->alias([
            'ensure.shift.open' => \App\Http\Middleware\EnsureShiftOpen::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            // AJAX calls (e.g. POS checkout) must get JSON errors too, otherwise
            // a validation failure becomes a redirect the frontend cannot parse.
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
