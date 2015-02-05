<?php namespace C4tech\Foundation;

use Illuminate\Support\ServiceProvider;

class FoundationServiceProvider extends ServiceProvider
{
    /**
     * @inheritDoc
     */
    protected $defer = false;

    /**
     * @inheritDoc
     */
    public function register()
    {
        //
    }

    /**
     * @inheritDoc
     */
    public function provides()
    {
        return [];
    }
}
