<?php

namespace Acelle\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use URL;
use Acelle\Model\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        ini_set('memory_limit', '-1');
        ini_set('pcre.backtrack_limit', 1000000000);

        // Laravel 5.5 to 5.6 compatibility
        Blade::withoutDoubleEncoding();

        // Check if HTTPS (including proxy case)
        $isSecure = false;
        if (isset($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'on') == 0) {
            $isSecure = true;
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_PROTO'], 'https') == 0 || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_SSL'], 'on') == 0) {
            $isSecure = true;
        }

        if ($isSecure) {
            URL::forceScheme('https');
        }

        // HTTP or HTTPS
        // parse_url will return either 'http' or 'https'
        //$scheme = parse_url(config('app.url'), PHP_URL_SCHEME);
        //if (!empty($scheme)) {
        //    URL::forceScheme($scheme);
        //}

        // Fix Laravel 5.4 error
        // [Illuminate\Database\QueryException]
        // SQLSTATE[42000]: Syntax error or access violation: 1071 Specified key was too long; max key length is 767 bytes
        Schema::defaultStringLength(191);

        if (!\App::runningInConsole()) {
            // This is just a trick for getting Controller name in view
            // See https://stackoverflow.com/questions/29549660/get-laravel-5-controller-name-in-view
            // @todo: fix this anti-pattern
            app('view')->composer('*', function ($view) {
                $action = app('request')->route()->getAction();
                $controller = class_basename($action['controller']);
                list($controller, $action) = explode('@', $controller);
                $view->with(compact('controller', 'action'));
            });
        }

        // extend substring validator
        Validator::extend('substring', function ($attribute, $value, $parameters, $validator) {
            $tag = $parameters[0];
            if (strpos($value, $tag) === false) {
                return false;
            }

            return true;
        });
        Validator::replacer('substring', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':tag', $parameters[0], $message);
        });

        // License validator
        Validator::extend('license', function ($attribute, $value, $parameters, $validator) {
            return $value == '' || true;
        });

        // License error validator
        Validator::extend('license_error', function ($attribute, $value, $parameters, $validator) {
            return false;
        });
        Validator::replacer('license_error', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':error', $parameters[0], $message);
        });

        // Payment config load from database
        if (isInitiated()) {
            // update global configs
            try {
                config([
                    'cashier.gateway' => Setting::get('system.payment_gateway') ? Setting::get('system.payment_gateway') : config('cashier.gateway'),
                    'cashier.end_period_last_days' => Setting::get('system.end_period_last_days') ? Setting::get('system.end_period_last_days') : config('cashier.end_period_last_days'),
                    'cashier.renew_free_plan' => Setting::get('system.renew_free_plan') ? Setting::get('system.renew_free_plan') : config('cashier.renew_free_plan'),
                    'cashier.recurring_charge_before_days' => Setting::get('system.recurring_charge_before_days') ? Setting::get('system.recurring_charge_before_days') : config('cashier.recurring_charge_before_days'),
                ]);

                // per payment configs
                foreach (config('cashier.gateways') as $gw => $data) {
                    foreach ($data['fields'] as $key => $value) {
                        $con = Setting::get('payment.'.$gw.'.'.$key);
                        if ($con != null) {
                            config(['cashier.gateways.'.$gw.'.fields.'.$key => $con]);
                        }
                    }
                }
            } catch (\Exception $ex) {
                // anything here?
            }
        }
    }
}
