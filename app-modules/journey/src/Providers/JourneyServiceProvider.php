<?php

declare(strict_types=1);

namespace CampusOs\Journey\Providers;

use CampusOs\Core\Contracts\EnrolledSubjectsProvider;
use CampusOs\Journey\Support\JourneyEnrolledSubjectsProvider;
use Illuminate\Support\ServiceProvider;

class JourneyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EnrolledSubjectsProvider::class, JourneyEnrolledSubjectsProvider::class);
    }

    public function boot(): void {}
}
