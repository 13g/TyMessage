<?php
/**
 * Created by PhpStorm.
 * User: yinchao
 * Date: 2015/12/14
 * Time: 16:01
 */
namespace TyMessage;

use Illuminate\Support\ServiceProvider;

class MessageServiceProvider extends ServiceProvider{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/tymessage.php' => config_path('tymessage.php')
        ], 'config');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->bind('ty.message', function ($app) {
            return new MessagePusher($app['config']['ty']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('ty.message');
    }
}