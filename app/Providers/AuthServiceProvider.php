<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider; // ✅ ini yang benar
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->registerPolicies(); // sekarang tidak akan error

        Gate::define('admin', function ($user): bool {
            return $user->role === 'admin';
        });

        Gate::define('user', function ($user): bool {
            return $user->role === 'user';
        });
    }
}
