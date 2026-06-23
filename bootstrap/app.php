<?php

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\PreventBackHistoryCache;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => CheckRole::class,
        ]);

        $middleware->prependToGroup('web', PreventBackHistoryCache::class);

        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Fichier (photo) trop volumineux par rapport à la limite PHP : message clair.
        $exceptions->render(function (PostTooLargeException $e, $request) {
            return back()->withErrors(['photo' => 'Fichier trop volumineux. La photo doit faire moins de 2 Mo.']);
        });
    })->create();
