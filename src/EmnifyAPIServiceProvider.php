<?php

namespace BionConnection\EmnifyAPI;

use Illuminate\Support\ServiceProvider;

class EmnifyAPIServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'bionconnection');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'bionconnection');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
      /*  if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }*/
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
       // $this->mergeConfigFrom(__DIR__.'/../config/emnifyapi.php', 'emnifyapi');

        // Register the service the package provides.
        $this->app->singleton('emnifyapi', function ($app) {
            return new EmnifyAPI;
        });
        
         $this->app->booting(function() {

            $loader = \Illuminate\Foundation\AliasLoader::getInstance();

            $loader->alias('Emnifyapi', 'BionConnection\WhmcsAPI\Facades\Emnifyapi');
        });

        $this->publishes([
            dirname(__FILE__) . '/../config/emnifyapi.php' => config_path('emnifyapi.php'),
        ]);
        
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['emnifyapi'];
    }
    
    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/emnifyapi.php' => config_path('emnifyapi.php'),
        ], 'emnifyapi.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/bionconnection'),
        ], 'emnifyapi.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/bionconnection'),
        ], 'emnifyapi.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/bionconnection'),
        ], 'emnifyapi.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
