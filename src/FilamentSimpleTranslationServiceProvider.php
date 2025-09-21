<?php

namespace Aslnbxrz\FilamentTranslation;

use Illuminate\Support\ServiceProvider;

class FilamentSimpleTranslationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/filament-simple-translation.php',
            'filament-simple-translation'
        );
    }

    public function boot(): void
    {
        // Config publish
        $this->publishes([
            __DIR__ . '/../config/filament-simple-translation.php' => config_path('filament-simple-translation.php'),
        ], 'filament-simple-translation');

        $this->publishes([
            __DIR__ . '/../config/filament-simple-translation.php' => config_path('filament-simple-translation.php'),
        ], 'filament-simple-translation-config');

        // Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-simple-translation');
    }
}