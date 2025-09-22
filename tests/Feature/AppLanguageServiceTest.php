<?php

use Aslnbxrz\SimpleTranslation\Models\AppText;
use Aslnbxrz\SimpleTranslation\Services\AppLanguageService;
use Mockery as m;

afterEach(fn () => m::close());

it('calls translate on AppLanguageService (void method)', function () {
    $mock = m::mock(AppLanguageService::class);

    // Expect correct args, return nothing (void)
    $mock->shouldReceive('translate')
        ->once()
        ->withArgs(function ($appText, $code, $value) {
            return $appText instanceof AppText
                && $code === 'en'
                && $value === 'Hello';
        })
        ->andReturnNull(); // void

    $this->app->instance(AppLanguageService::class, $mock);

    $svc = app(AppLanguageService::class);
    $appText = new AppText();

    // Assert: no exception is thrown
    expect(fn () => $svc->translate($appText, 'en', 'Hello'))->not->toThrow(\Throwable::class);
});