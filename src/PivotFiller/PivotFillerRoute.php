<?php

namespace Larangular\PivotSupport\PivotFiller;

use Illuminate\Support\Facades\Route;

class PivotFillerRoute {

    public function route(string $name, string $class) {
        if (!app()->isLocal()) {
            return;
        }

        Route::group([
            'prefix'     => $name . '/pivot-filler',
            'middleware' => 'web',
            'as'         => $name.'.pivot-filler.',
        ], function () use($class) {
            Route::get('/', $class . '@index')->name('index');
            Route::get('show', $class . '@show')->name('show');
            Route::post('store', $class . '@store')->name('store');
            Route::post('create-local', $class . '@localModelStore')->name('create-local');
        });
    }

}
