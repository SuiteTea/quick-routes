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
    public function register($name, $routes = [], $default = [])
    {
        if (empty($routes)) {
            trigger_error('No routes specified', E_USER_WARNING);
            return null;
        }

        $default = $this->determineDefault($default);

        if (empty($default)) {
            trigger_error('No default routes exist', E_USER_WARNING);
            return null;
        }

        $routes = $this->determineRoutes($routes, $default);

        $this->route($name, $routes, $default);
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
        $this->router->group(['prefix' => $name], function() use ($name, $routes, $default)
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

                $method = isset($method) ? $method : 'get';

                $route = $this->router->$method($pattern, [
                    'as' => $this->determineRouteName($name, $route, $method),
                    'uses' => $this->determineController($name, $route, $method)
                ]);

                if (isset($where)) {
                    $route->where($where);
                }
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
        return ! empty($default)
               ? $default
               : $this->default;
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
        $routes = is_string($routes) ? ['', $routes] : $routes;
        return array_search('*', $routes)
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
        $route_name = strtolower($name.'.'.$route);
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
        $controller = ucfirst($name).'Controller';
        $method = camel_case($prefix.'_'.$route);

        return $controller.'@'.$method;
    }
}