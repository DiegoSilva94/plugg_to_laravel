<?php
namespace PluggTo\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application as LaravelApplication;
use PluggTo\Commands\SincronizarPedido;
use PluggTo\Commands\SincronizarProduto;

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
        $source = realpath(__DIR__.'/../../resources/config/pluggTo.php');
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole())
            $this->publishes([$source => config_path('pluggTo.php')]);
        $this->mergeConfigFrom($source, 'pluggTo');
        $this->setupMigrations($this->app);
    }
    /**
     * Setup the migrations.
     *
     * @param \Illuminate\Contracts\Foundation\Application $app
     *
     * @return void
     */
    protected function setupMigrations(Application $app)
    {
        $source = realpath(__DIR__.'/../../resources/database/migrations/');

        if ($app instanceof LaravelApplication && $app->runningInConsole())
            $this->publishes([$source => database_path('migrations')], 'migrations');
    }
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands(SincronizarPedido::class);
        $this->commands(SincronizarProduto::class);
        
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