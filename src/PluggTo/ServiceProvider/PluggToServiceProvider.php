<?php
namespace PluggTo\ServiceProvider;
use Illuminate\Support\ServiceProvider;
/**
 * Class RepositoryServiceProvider
 * @package Prettus\Repository\Providers
 */
class PluggToServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    /**
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../resources/config/pluggTo.php' => config_path('pluggTo.php')
        ]);
        $this->mergeConfigFrom(__DIR__ . '/../../resources/config/pluggTo.php', 'pluggTo');
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
    }
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}