<?php

namespace Larangular\PivotSupport\PivotFiller;

use Illuminate\Support\Facades\Route;

class PivotFillerRoute {

    public function route(string $name, string $class) {
        if (!app()->isLocal()) {
            return;
        }

        Route::prefix($name.'/pivot-filler')
             ->middleware('web')
             ->group(function () use ($class) {
                Route::get('/', $class . '@PivotFillerIndex');
            });
    }

}
