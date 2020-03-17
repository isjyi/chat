<?php


namespace App\Providers;


use App\Facade\Repositories\User;
use App\Repositories\UserRepositoryEloquent;
use Illuminate\Support\ServiceProvider;

class FacadeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('userRepo', function ($app) {
            return new UserRepositoryEloquent($app);
        });
    }
}
