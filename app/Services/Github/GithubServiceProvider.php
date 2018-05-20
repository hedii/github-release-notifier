<?php

namespace App\Services\Github;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class GithubServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     */
    public function register(): void
    {
        $this->app->singleton(Github::class, function (Application $app) {
            return new Github(config('github-release-notifier'), $app['cache']->getStore());
        });
    }
}
