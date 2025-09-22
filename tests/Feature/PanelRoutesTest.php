<?php

use Illuminate\Support\Facades\Route;

it('registers panel routes', function () {
    $all = collect(Route::getRoutes())->map(fn($r) => $r->uri())->all();
    expect($all)->toContain('admin'); // panel path exists

    // If your page has a specific route name/uri, assert it:
    // expect($all)->toContain('admin/translations');
});