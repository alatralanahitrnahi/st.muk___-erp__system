<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem; // ✅ Add this

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('files', fn() => new Filesystem());
    }
    public function boot(): void
    {
        //
    }
}
