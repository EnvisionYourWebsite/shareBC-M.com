<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::missing(function()
{
    $error = new Sule\Kotakin\Controllers\Error(
        App::make('kotakin'), 
        App::make('sentry')
    );

    return $error->_404();
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('admin.login', function()
{
    $admin = new Sule\Kotakin\Filters\Admin(App::make('sentry'));

    return $admin->login();
});

Route::filter('admin.auth', function()
{
    $admin = new Sule\Kotakin\Filters\Admin(App::make('sentry'));

    return $admin->auth();
});

Route::filter('account.login', function()
{
    $account = new Sule\Kotakin\Filters\Account(App::make('sentry'));

    return $account->login();
});

Route::filter('account.auth', function()
{
    $account = new Sule\Kotakin\Filters\Account(App::make('sentry'));

    return $account->auth();
});

/*
|--------------------------------------------------------------------------
| Define View Composer
|--------------------------------------------------------------------------
|
| We set all default view data in here
|
*/

View::composer('*', function($view)
{
    try {
        $user = DB::table('users')->first();
        
        $composer = new Sule\Kotakin\Composers\DefaultViewData(
            App::make('kotakin'), 
            App::make('sentry')
        );

        return $composer->compose($view);
    } catch (Exception $e) {}
});
