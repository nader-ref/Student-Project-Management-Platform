<?php

namespace App\Providers;

use App\Services\StudentEnrollmentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (! $this->app->runningInConsole() && request()->getSchemeAndHttpHost()) {
            URL::forceRootUrl(request()->getSchemeAndHttpHost());
        }

        View::composer('student.partials.navbar', function ($view) {
            $enrollment = StudentEnrollmentService::resolve(null, Auth::user());

            $view->with('enrollmentMode', $enrollment['mode']);
            $view->with('enrolledProject', $enrollment['project']);
        });
    }
}
