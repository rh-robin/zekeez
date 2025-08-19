<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

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
        Socialite::extend('apple', static function ($app) {
            $config = $app['config']['services.apple'];
            return new \SocialiteProviders\Apple\Provider(
                $app['request'],
                $config['client_id'],
                $config['client_secret'],
                $config['redirect']
            );
        });

        Validator::extend('exists_polymorphic', function ($attribute, $value, $parameters, $validator) {
            $data = $validator->getData();
            // Get the corresponding type field (e.g., properties.0.type for properties.0.id)
            $typeField = str_replace('.id', '.type', $attribute);
            $typeValue = data_get($data, $typeField);

            if ($typeValue === 'App\Models\Building') {
                return \App\Models\Building::where('id', $value)->exists();
            } elseif ($typeValue === 'App\Models\Unit') {
                return \App\Models\Unit::where('id', $value)->exists();
            }

            return false;
        }, 'The :attribute does not exist in the specified table.');

    }
}
