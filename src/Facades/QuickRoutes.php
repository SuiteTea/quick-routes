<?php namespace SuiteTea\QuickRoutes\Facades;

use Illuminate\Support\Facades\Facade;

class QuickRoutes extends Facade {

    protected static function getFacadeAccessor() 
    {
        return 'suitetea.quickroutes'; 
    }
}