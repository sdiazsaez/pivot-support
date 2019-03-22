<?php

namespace Larangular\PivotSupport;

use Illuminate\Support\ServiceProvider;
use Larangular\PivotSupport\PivotFiller\PivotFillerRoute;

class PivotSupportServiceProvider extends ServiceProvider {

    public function boot() {
    }

    public function register() {
        $this->app->singleton(PivotFillerRoute::class, function () {
            return new PivotFillerRoute();
        });

    }

    public function provides() {
        return [
            PivotFillerRoute::class,
        ];
    }
}
