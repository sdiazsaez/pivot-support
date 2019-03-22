<?php

namespace Larangular\PivotSupport\PivotFiller;

use Illuminate\Support\Facades\Route;

class PivotFillerRoute {

    public function route(string $name, string $class) {
        if (!app()->isLocal()) {
            return;
        }

        Route::prefix('pivot-filler/'.$name)
             ->middleware('web')
             ->group(function () use ($class) {
                Route::get('index', $class . '@PivotFillerIndex');
            });
    }

}
