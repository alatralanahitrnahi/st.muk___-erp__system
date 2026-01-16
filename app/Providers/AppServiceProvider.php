<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Database\DatabaseServiceProvider;
use Illuminate\Cache\CacheServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Manually register core service providers
        $this->app->register(FilesystemServiceProvider::class);
        $this->app->register(DatabaseServiceProvider::class);
        $this->app->register(CacheServiceProvider::class);
    }

    public function boot(): void
    {
        //
    }
}
