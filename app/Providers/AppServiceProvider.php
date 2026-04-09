<?php

namespace App\Providers;

use App\Actions\SyncUserRolesFromLanCore;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        $this->configureDefaults();
        $this->configureLanCoreRoleSync();
    }

    /**
     * Sync roles from LanCore after every standard login.
     *
     * SSO logins are handled by the callback controller which passes the roles
     * directly from the exchange response (no second request). To avoid
     * a redundant API call for those users, we skip the sync when
     * lancore_synced_at was updated within the last 30 seconds.
     */
    private function configureLanCoreRoleSync(): void
    {
        Event::listen(function (Login $event) {
            if (! ($event->user instanceof User)) {
                return;
            }

            $user = $event->user;

            if ($user->lancore_synced_at !== null && $user->lancore_synced_at->diffInSeconds() < 30) {
                return;
            }

            app(SyncUserRolesFromLanCore::class)->handle($user);
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
