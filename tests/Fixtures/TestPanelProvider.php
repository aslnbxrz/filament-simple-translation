<?php

namespace Tests\Fixtures;

use Aslnbxrz\FilamentSimpleTranslation\FilamentSimpleTranslatePlugin;
use Filament\Panel;
use Filament\PanelProvider;

class TestPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->plugin(FilamentSimpleTranslatePlugin::make());
    }
}