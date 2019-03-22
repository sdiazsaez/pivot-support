<?php

namespace Larangular\PivotSupport\Facades;

use Illuminate\Support\Facades\Facade;

class PivotFillerRoute extends Facade {
    protected static function getFacadeAccessor() {
        return \Larangular\PivotSupport\PivotFiller\PivotFillerRoute::class;
    }
}
