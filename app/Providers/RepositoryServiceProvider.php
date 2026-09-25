<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Admin\Repositories\Contracts\SyncRepositoryInterface;
use App\Modules\Admin\Repositories\Eloquent\SyncRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services
     *
     * @return void
     */
    public function register()
    {
        $this->bindRepositories();
    }

    /**
     * Bootstrap services
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Bind repositories to their interfaces
     *
     * @return void
     */
    protected function bindRepositories()
    {

        $this->app->bind(
            SyncRepositoryInterface::class,
            SyncRepository::class
        );

        // Add more repository bindings here as needed
        // Example:
        // $this->app->bind(
        //     UserRepositoryInterface::class,
        //     UserRepository::class
        // );
    }
}
