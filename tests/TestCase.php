<?php

namespace Tests;

use Aslnbxrz\FilamentSimpleTranslation\FilamentSimpleTranslationServiceProvider;
use Aslnbxrz\SimpleTranslation\SimpleTranslationServiceProvider;
use Filament\FilamentServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Tests\Fixtures\TestPanelProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            // Livewire & Filament
            LivewireServiceProvider::class,
            FilamentServiceProvider::class,

            // Core package
            SimpleTranslationServiceProvider::class,

            // Plugin provider
            FilamentSimpleTranslationServiceProvider::class,

            // Test panel provider
            TestPanelProvider::class,
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        // Minimal users table (if panel requires auth)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $app['config']->set('app.locale', 'en');

        // DB
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
