<?php

namespace Aslnbxrz\FilamentSimpleTranslation;

use Aslnbxrz\FilamentSimpleTranslation\Filament\Pages\TranslationsPage;
use Filament\Contracts\Plugin;
use Filament\Panel;


class FilamentSimpleTranslatePlugin implements Plugin
{
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'filament-simple-translate';
    }

    public function register(Panel $panel): void
    {
        $panel->pages([TranslationsPage::class]);
    }

    public function boot(Panel $panel): void
    {
        //
    }
}