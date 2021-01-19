<?php

declare(strict_types=1);

namespace App\Providers;

use App\Authentication\Domain\AuthorizationSession;
use App\Authentication\Domain\AuthServer;
use App\Authentication\Domain\AuthServerInterface;
use App\Http\ExactAuthClient;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use function config;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(AuthServerInterface::class, fn() => new AuthServer(
            new ExactAuthClient(),
            (string) config('exact.auth.client_id'),
            (string) config('exact.auth.client_secret'),
            (string) config('exact.auth.redirect_uri'),
        ));

        if (!$this->app->environment('testing')) {
            $this->app->singleton(AuthorizationSession::class, fn() => new AuthorizationSession(
                Cache::store('redis')
            ));
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
