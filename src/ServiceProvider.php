<?php namespace C4tech\Support;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    protected $configPath = '';

    /**
     * @inheritDoc
     */
    public function __construct($app)
    {
        $this->configPath = __DIR__ . '/../resources/config.php';
        parent::__construct($app);
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        $configs = [];
        $configs[$this->configPath] = config_path('c4tech.php');
        $this->publishes($configs);
    }

    /**
     * @inheritDoc
     */
    public function register()
    {
        $this->mergeConfigFrom($this->configPath, 'c4tech');
    }
}
