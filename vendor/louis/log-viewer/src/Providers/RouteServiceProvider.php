<?php

namespace Acelle\Extra\LogViewer\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define the routes for the application.
     *
     * @param \Illuminate\Routing\Router $router
     */
    public function boot()
    {
        parent::boot();
        $this->loadViewsFrom(realpath(__DIR__.'/../../resources/views'), 'log-viewer');
    }
    
    /**
     * Define the routes for the application.
     *
     * @param \Illuminate\Routing\Router $router
     */
    public function map()
    {
        Route::group([
            'prefix' => 'log-viewer',
            'namespace' => 'Acelle\\Extra\\LogViewer\\Http\\Controllers',
            'middleware' => ['web_nocsrf', 'auth', 'not_installed', 'backend'], ], function ($router) {
                Route::get('/', 'LogViewerController@index')->name('log-viewer');
                Route::post('{file}/empty', 'LogViewerController@emptyFile');
                Route::get('filemanager', 'LogViewerController@filemanager');
                Route::post('filemanager', 'LogViewerController@filemanager')->name('log-viewer.filemanager');
            }
        );
    }
}
