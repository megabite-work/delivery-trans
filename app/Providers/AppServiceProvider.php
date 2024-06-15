<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (App::environment('production')) {
            URL::forceScheme('https');
        }
        Validator::extend('morph_exists', function ($attribute, $value, $parameters, $validator) {
            if (! $type = Arr::get($validator->getData(), $parameters[0], false)) {
                return false;
            }

            $type = Relation::getMorphedModel($type) ?? $type;

            if (!class_exists($type)) {
                return false;
            }

            return resolve($type)->where('id', $value)->exists();
        });
    }
}
