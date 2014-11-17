<?php namespace SuiteTea\QuickRoutes;

use Illuminate\Foundation\Application;

class QuickRoutes {

    /**
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * @var \Illuminate\Routing\Router
     */
    protected $router;

    /**
     * Default Routes
     * @var array
     */
    protected $default = [];

    /**
     * Temp Set
     * @var array
     */
    protected $temp_set = [];

    /**
     * Temp Controller Name
     * @var string
     */
    protected $controller;

    /**
     * Start this bad boy up!
     * 
     * @param Application $app 
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->router = $app['router'];
        $this->default = $this->app['config']->get('quickroutes::default');
    }

    /**
     * Get Default
     *
     * Gets the default routes
     * 
     * @return array
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * Set Default
     *
     * Allows default routes to be overwritten from a certain point in time.
     * 
     * @param array $default
     */
    public function setDefault(array $default = [])
    {
        $this->default = $default;
        return $this;
    }

    public function with(array $set = [])
    {
        $this->temp_set = $set;
        return $this;
    }

    /**
     * Set a temporary controller name
     *
     * @param $controller
     */
    public function setController($controller)
    {
        $this->controller = (string)$controller;
    }

    /**
     * Get the temporarily set controller
     *
     * @return string
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Register
     *
     * Registers the requested routes with the appropriate controller
     * 
     * @param string $name The name of the controller
     * @param array $routes The routes to register from the default routes
     * @param array $default Optional parameter to overwrite the default routes array
     * @return void
     */
    public function register($name, $routes = null, $default = [])
    {
        $default = $this->determineDefault($default);

        if (empty($default)) {
            trigger_error('No default routes exist', E_USER_WARNING);
            return null;
        }

        $routes = $this->determineRoutes($routes, $default);

        $this->route($name, $routes, $default);

        // Remove a temporarily set controller name
        $this->controller = null;
    }

    /**
     * Route
     *
     * Loops through all requested routes and creates a new
     * group entry with the App Router to the appropriate controller
     * and method.
     * 
     * @param string $name
     * @param array $routes
     * @param array $default
     * @return void
     */
    protected function route($name, $routes, $default)
    {
        $prefix = $name;
        $name = preg_replace('/[^A-Za-z0-9- ]{1,}/', '_', $name);
        
        $this->router->group(['prefix' => $prefix], function() use ($name, $routes, $default)
        {
            foreach ($routes as $route) {
                if (! isset($default[$route])) {
                    trigger_error(
                        sprintf(
                            'Route index "%s" is not defined in the route list provided (%s)',
                            $route,
                            implode(', ', array_keys($default))
                        ),
                        E_USER_WARNING
                    );
                    return null;
                }
                extract($default[$route]);

                $methods = isset($methods) ? $methods : ['get'];
				
				if (!is_array($methods)) {
					$methods = array($methods);
				}



				foreach ($methods as $method) {
					$compiled_route = $this->router->$method($pattern, [
					 	'as' => $this->determineRouteName($name, $route, $method),
                    	'uses' => isset($uses) ? $uses : $this->determineController($name, $route, $method)
					]);
					
					if (isset($where)) {
						 $compiled_route->where($where);
					}
				}

                unset($methods);
            }
        });
    }

    /**
     * Determine Default
     *
     * Determines whether to use user passed routes or default.
     * 
     * @param array $default
     * @return array
     */
    protected function determineDefault($default)
    {
        $default = ! empty($default)
                   ? $default
                   : $this->default;

        $set = array_merge($default, $this->temp_set);
        $this->temp_set = [];

        return $set;
    }

    /**
     * Determine Routes
     *
     * Determines if the wildcard shortcut for all routes is used,
     * or to just use the user passed list of routes.
     * 
     * @param string|array $routes Either wildcard '*' or list of routes
     * @param [type] $default
     * @return array
     */
    protected function determineRoutes($routes, $default)
    {
        $routes = $routes === '*' ? null : $routes;
        return is_null($routes)
               ? array_keys($default)
               : $routes;
    }

    /**
     * Determine Route Name
     *
     * Names all routes as a combination of controller, 
     * route name, and HTTP request type.
     * 
     * @param string $name controller name
     * @param string $route Route name
     * @param string $method HTTP request method
     * @return string The fully named route in dot notation.
     */
    protected function determineRouteName($name, $route, $method)
    {
        $route_name = strtolower($name ? $name.'.'.$route : $route);
        return $method != 'get' ? $route_name.'.'.$method : $route_name;
    }

    /**
     * Determine Controller
     *
     * Combines the name of the controller, route name, 
     * and prefix (HTTP request method) into a Laravel acceptable 
     * route name (ex: "SomeController@getIndex")
     * 
     * @param string $name
     * @param string $route
     * @param string $prefix 
     * @return string
     */
    protected function determineController($name, $route, $prefix)
    {
        $controller = $this->controller ?: studly_case($name).'Controller';
        $method = camel_case($prefix.'_'.$route);

        return $controller.'@'.$method;
    }
}