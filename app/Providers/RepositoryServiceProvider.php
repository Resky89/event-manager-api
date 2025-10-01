<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\{UserRepositoryInterface, EventRepositoryInterface, TicketRepositoryInterface};
use App\Repositories\Eloquent\{UserRepository, EventRepository, TicketRepository};

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(TicketRepositoryInterface::class, TicketRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
