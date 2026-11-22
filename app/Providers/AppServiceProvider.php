<?php

namespace App\Providers;

use Illuminate\Foundation\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // A Cloudflared request cannot reach the local Vite HMR server. When
        // the app is opened through a tunnel, force Laravel to use the built
        // assets even if a local `public/hot` file still exists.
        if (! app()->runningInConsole() && request()->getHost() !== 'localhost'
            && ! in_array(request()->getHost(), ['127.0.0.1', '::1'], true)) {
            app(Vite::class)->useHotFile(storage_path('framework/vite-tunnel-hot'));
        }
    }
}
