<?php

namespace App\Providers;

use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();

        // ログインユーザに許可
        Gate::define('user', function ($user) {
            return ($user->role_id == 1 || $user->role_id == 2);
        });
        // 管理者に許可
        Gate::define('admin', function ($user) {
            return ($user->role_id == 1);
        });
    }
}
