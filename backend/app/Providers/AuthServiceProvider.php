<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use PaqSuite\LaravelCore\Security\AccesoTotalChecker;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('seguridadAdmin', function (?User $user): bool {
            if ($user === null) {
                return false;
            }

            $empresaId = (int) (request()->header(config('paqsuite.headers.company', 'X-Company-Id')) ?: 0);
            if ($empresaId <= 0) {
                $empresaId = 1;
            }

            return app(AccesoTotalChecker::class)->hasAccesoTotal((int) $user->id, $empresaId);
        });
    }
}
