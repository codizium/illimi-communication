<?php

namespace Illimi\Communication;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

class CommunicationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('illimi-communication', function () {
            return new IllimiCommunication();
        });

        $this->mergeConfigFrom(
            __DIR__ . '/../config/communication.php',
            'illimi-communication'
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'illimi-communication');

        Route::middleware('web')->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });

        Route::middleware($this->apiRouteMiddleware())->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });

        $this->publishes([
            __DIR__ . '/../config/communication.php' => config_path('illimi-communication.php'),
        ], 'illimi-communication-config');
    }

    protected function apiRouteMiddleware(): array
    {
        $middleware = ['api'];

        if (class_exists(EnsureFrontendRequestsAreStateful::class)) {
            $middleware[] = EnsureFrontendRequestsAreStateful::class;
        }

        return $middleware;
    }
}
