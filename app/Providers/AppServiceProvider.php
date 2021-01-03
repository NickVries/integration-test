<?php
declare(strict_types=1);
namespace App\Providers;

use App\Authentication\AuthServer;
use App\Authentication\AuthServerInterface;
use GuzzleHttp\Client;
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
        $this->app->singleton(AuthServerInterface::class, fn () => new AuthServer(
            new Client([
                'base_uri' => config('exact.api.base_uri')
            ]),
            (string) config('exact.auth.client_id'),
            (string) config('exact.auth.client_secret'),
            (string) config('exact.auth.redirect_uri'),
        ));
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
