<?php namespace C4tech\Support;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * @inheritDoc
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/resources/config.php' => config_path('c4tech.php'),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/resources/config.php', 'c4tech');
    }
}
