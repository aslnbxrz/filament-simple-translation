<?php

namespace Aslnbxrz\FilamentSimpleTranslation;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentSimpleTranslationServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-simple-translation';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name)->hasViews();
    }

    public function packageBooted(): void {}
}