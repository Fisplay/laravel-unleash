<?php

namespace JWebb\Unleash\Providers;

use JWebb\Unleash\Unleash;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider as IlluminateServiceProvider;
use Laravel\Lumen\Application as Lumen;

class ServiceProvider extends IlluminateServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('unleash', function ($app) {
            $client = new Client([
                'headers' => [
                    'UNLEASH-APPNAME' => config('unleash.application_name'),
                    'UNLEASH-INSTANCEID' => config('unleash.instance_id'),
                ]
            ]);
            return new Unleash($client);
        });

        $this->app->alias('unleash', Unleash::class);

        $this->mergeConfigFrom($this->getConfigPath(), 'unleash');
    }

    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if (! config('unleash.enabled')) {
            return;
        }

        if ($this->app instanceof Lumen) {
            return;
        }

        $this->publishes([
            $this->getConfigPath() => config_path('unleash.php'),
        ]);
    }

    /**
     * Get the path to the config.
     *
     * @return string
     */
    private function getConfigPath(): string
    {
        return __DIR__ . '/../../config/unleash.php';
    }
}
