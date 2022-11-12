<?php
/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */

namespace App\Providers;

use App\Policies\FilterPolicy;
use App\Policies\ImportPolicy;
use App\Innoclapps\Models\Filter;
use App\Innoclapps\Models\Import;
use App\Policies\DashboardPolicy;
use App\Innoclapps\Models\Dashboard;
use App\Policies\OAuthAccountPolicy;
use Illuminate\Support\Facades\Gate;
use App\Innoclapps\Models\OAuthAccount;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Filter::class       => FilterPolicy::class,
        Dashboard::class    => DashboardPolicy::class,
        OAuthAccount::class => OAuthAccountPolicy::class,
        Import::class       => ImportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(fn ($user, $ability) => $user->isSuperAdmin() ? true : null);
        Gate::define('is-super-admin', fn ($user) => $user->isSuperAdmin());
        Gate::define('access-api', fn ($user) => $user->hasApiAccess());
    }
}
