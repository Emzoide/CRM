<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('pdf', function ($app) {
            return new Pdf();
        });
    }

    public function boot()
    {
        //
    }
}
