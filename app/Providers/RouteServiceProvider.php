<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::prefix('api/admin')
                ->middleware(['api', 'is_admin'])
                ->namespace($this->namespace)
                ->group(base_path('routes/admin.php'));

            Route::prefix('api/provider')
                ->middleware(['api', 'is_provider'])
                ->namespace($this->namespace)
                ->group(base_path('routes/provider.php')); 
                
            Route::prefix('api/client')
                ->middleware(['api'])
                ->namespace($this->namespace)
                ->group(base_path('routes/client.php'));

            Route::prefix('api/auth')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/auth.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
