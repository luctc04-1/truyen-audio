<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Admin\Repositories\Contracts\SyncRepositoryInterface;
use App\Modules\Admin\Repositories\Eloquent\SyncRepository;
use App\Modules\Story\Repositories\Contracts\StoryRepositoryInterface;
use App\Modules\Story\Repositories\Eloquent\StoryRepository;

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
        // Story Repository
        $this->app->bind(
            StoryRepositoryInterface::class,
            StoryRepository::class
        );

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
