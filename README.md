# Quick Routes for Laravel

Do you ever find yourself declaring the same types of routes over and over again through multiple controllers even though they all do and look basically the same thing/way? That's where Quick Routes comes in. 

Turn this:

```
Route::get('users', 'UserController@index');
Route::post('users', array(
    'as' => 'user.create',
    'uses' => 'UserController@create'
));
Route::post('users/{id}', array(
    'as' => 'user.edit',
    'uses' => 'UserController@update'
))->where(array(
    'id' => '[0-9]+'
));
Route::get('users/{id}/delete', array(
    'as' => 'user.delete',
    'uses' => 'UserController@delete'
))->where(array(
    'id' => '[0-9]+'
));

Route::get('tweets', 'TweetController@index');
Route::post('tweets', array(
    'as' => 'tweet.create',
    'uses' => 'TweetController@create'
));
Route::post('tweets/{id}', array(
    'as' => 'tweet.edit',
    'uses' => 'TweetController@update'
))->where(array(
    'id' => '[0-9]+'
));
Route::get('tweets/{id}/delete', array(
    'as' => 'tweet.delete',
    'uses' => 'TweetController@delete'
))->where(array(
    'id' => '[0-9]+'
));
```

into this:

```
QuickRoutes::register('users', ['index', 'create', 'edit', 'delete']);
QuickRoutes::register('tweets', '*');
```
&#42;you can specify which routes to use, or simply use all ("&#42;")

---

The magic happens by setting default routes in an array either through a config file or setting it.

There are multiple ways to set the default routes.

1) The package config found in "app/config/suitetea/quick-routes" (available after publish package config).
2) Calling `QuickRoutes::setDefault()` and passing in a properly formatted array.
3) Overriding the defaults per register call. There is an option third parameter part of `register` that you can pass routes to be used instead of the global defaults.

---

Additionally, you can group routes by using different default sets. Ex:

```
$set_1 = array(); // Declare your routes here
QuickRoutes::setDefault($set_1); // Register calls after this will use $set_1
QuickRoutes::register('users', ['route_1', 'route_3']);
QuickRoutes::register('someroute', '*');

$set_2 = array(); // Another set of routes
QuickRoutes::setDefault($set_2); // Register calls after this will use $set_2
QuickRoutes::register('anotherroute', ['foo', 'bar']);
```

---

## New in 0.6.0

Prefixed routes can now be passed. The prefix will be used to group the routes.

Non alphanumeric characters in the route name are replaced with an underscore, and `studly_case` is used for the controller.

Example:

```
QuickRoutes::register('user/groups', '*');
```

This will generate a route pointing to `UserGroupsController@someMethod`.

This will also prefix the named routes with "user_groups". Ex: `user_groups.index`


## New in 0.7.0

You can now create multiple methods per route, just pass the methods parameter as an array.

# Installation

Via Composer

```
{
    "require": {
        "suitetea/quick-routes": "dev-develop"
    }
}
```

The default routes array should be in the following format:

```
$default_routes = [
   'index' => [
       'pattern' => '/',
   ],
   'create' => [
       'pattern' => 'create',
       'methods' => ['get','post']
   ],
   'edit' => [
       'pattern' => '{id}/edit',
       'where' => ['id' => '[0-9]+'],
       'methods' => ['get','post']
   ],
   'view' => [
       'pattern' => '{id}',
       'where' => ['id' => '0-9]+']
   ]
];
```

Optionally, you can publish the package config file and set default routes there.

```
php artisan config:publish suitetea/quick-routes
```