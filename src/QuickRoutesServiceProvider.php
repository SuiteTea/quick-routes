<?php namespace SuiteTea\QuickRoutes;

use Illuminate\Support\ServiceProvider;

class QuickRoutesServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->package('suitetea/quickroutes');
    }

    public function register()
    {
        $this->app['suitetea.quickroutes'] = $this->app->share(function($app)
        {
            return new QuickRoutes($app);
        });

        $this->app->booting(function()
        {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('QuickRoutes', 'SuiteTea\QuickRoutes\Facades\QuickRoutes');
        });
    }

}