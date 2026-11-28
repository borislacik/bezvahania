<?php

namespace App\Providers;

use App\Models\Setting;
use App\Support\BundlePricing;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(BundlePricing::class);
    }

    public function boot(): void
    {
        RateLimiter::for('bookings', fn (Request $request): Limit => Limit::perMinute(5)->by((string) $request->ip()));
        RateLimiter::for('contact', fn (Request $request): Limit => Limit::perMinute(3)->by((string) $request->ip()));

        // Contact details and operator data (admin settings) for every public view.
        View::composer(['pages.*', 'components.layouts.public', 'components.site.*'], function (ViewInstance $view): void {
            $view->with('settings', Setting::allValues());
        });

        // Bundle of two workshops (prices and partner course) for the website and the e-mails.
        View::composer(['pages.*', 'components.site.*', 'mail.*'], function (ViewInstance $view): void {
            $view->with('bundle', app(BundlePricing::class));
        });
    }
}
