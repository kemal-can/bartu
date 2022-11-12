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

use App\Innoclapps\Facades\Menu;
use Illuminate\Support\Facades\View;
use App\Innoclapps\Facades\Innoclapps;
use App\Innoclapps\Workflow\Workflows;
use Illuminate\Support\Facades\Schema;
use App\Innoclapps\Facades\Permissions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use App\Innoclapps\Menu\Item as MenuItem;
use App\Contracts\Repositories\UserRepository;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Support\ResourceCommonPermissionsProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
    * All of the container bindings that should be registered.
    *
    * @var array
    */
    public $bindings = [
        \App\Contracts\Repositories\UserRepository::class            => \App\Repositories\UserRepositoryEloquent::class,
        \App\Contracts\Repositories\NoteRepository::class            => \App\Repositories\NoteRepositoryEloquent::class,
        \App\Contracts\Repositories\CallRepository::class            => \App\Repositories\CallRepositoryEloquent::class,
        \App\Contracts\Repositories\CallOutcomeRepository::class     => \App\Repositories\CallOutcomeRepositoryEloquent::class,
        \App\Contracts\Repositories\SourceRepository::class          => \App\Repositories\SourceRepositoryEloquent::class,
        \App\Contracts\Repositories\CompanyRepository::class         => \App\Repositories\CompanyRepositoryEloquent::class,
        \App\Contracts\Repositories\ContactRepository::class         => \App\Repositories\ContactRepositoryEloquent::class,
        \App\Contracts\Repositories\IndustryRepository::class        => \App\Repositories\IndustryRepositoryEloquent::class,
        \App\Contracts\Repositories\PhoneRepository::class           => \App\Repositories\PhoneRepositoryEloquent::class,
        \App\Contracts\Repositories\WebFormRepository::class         => \App\Repositories\WebFormRepositoryEloquent::class,
        \App\Contracts\Repositories\UserInvitationRepository::class  => \App\Repositories\UserInvitationRepositoryEloquent::class,
        \App\Contracts\Repositories\TeamRepository::class            => \App\Repositories\TeamRepositoryEloquent::class,
        \App\Contracts\Repositories\CommentRepository::class         => \App\Repositories\CommentRepositoryEloquent::class,
        \App\Contracts\Repositories\ActivityTypeRepository::class    => \App\Repositories\ActivityTypeRepositoryEloquent::class,
        \App\Contracts\Repositories\ActivityRepository::class        => \App\Repositories\ActivityRepositoryEloquent::class,
        \App\Contracts\Repositories\DealRepository::class            => \App\Repositories\DealRepositoryEloquent::class,
        \App\Contracts\Repositories\PipelineRepository::class        => \App\Repositories\PipelineRepositoryEloquent::class,
        \App\Contracts\Repositories\StageRepository::class           => \App\Repositories\StageRepositoryEloquent::class,
        \App\Contracts\Repositories\LostReasonRepository::class      => \App\Repositories\LostReasonRepositoryEloquent::class,
        \App\Contracts\Repositories\ProductRepository::class         => \App\Repositories\ProductRepositoryEloquent::class,
        \App\Contracts\Repositories\BillableRepository::class        => \App\Repositories\BillableRepositoryEloquent::class,
        \App\Contracts\Repositories\BillableProductRepository::class => \App\Repositories\BillableProductRepositoryEloquent::class,
        \App\Contracts\Repositories\CalendarRepository::class        => \App\Repositories\CalendarRepositoryEloquent::class,
        \App\Contracts\Repositories\SynchronizationRepository::class => \App\Repositories\SynchronizationRepositoryEloquent::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Model::preventLazyLoading(! $this->app->isProduction());
        Schema::defaultStringLength(191);
        JsonResource::withoutWrapping();

        $this->app['config']->set('innoclapps.resources.permissions.common', ResourceCommonPermissionsProvider::class);

        $this->app['config']->set('innoclapps.user_repository', UserRepository::class);

        $this->registerMenuItems();
        $this->performBasicConfiguration();
        $this->bootApplication();
    }

    /**
     * Register the menu items that should be displayed on the sidebar
     *
     * @return void
     */
    protected function registerMenuItems()
    {
        Innoclapps::booting(function () {
            Menu::register(MenuItem::make(__('dashboard.insights'), '/dashboard', 'ChartSquareBar')
                ->position(35));
        });
    }

    /**
     * Perform basic application configuration
     *
     * @return void
     */
    protected function performBasicConfiguration()
    {
        if (Innoclapps::isAppInstalled()) {
            $this->configureVoIP();
        }
    }

    /**
    * Set the application VoIP Client
    */
    protected function configureVoIP()
    {
        $options     = $this->app['config']->get('innoclapps.services.twilio');
        $totalFilled = count(array_filter($options));

        if ($totalFilled === count($options)) {
            $this->app['config']->set('innoclapps.voip.client', 'twilio');

            Permissions::group(['name' => 'voip', 'as' => __('call.voip_permissions')], function ($manager) {
                $manager->register('view', [
                    'as'          => __('role.capabilities.use_voip'),
                    'permissions' => ['use voip' => __('role.capabilities.use_voip')],
                ]);
            });
        }
    }

    /**
     * Boot application
     *
     * The app.php is the main file and is loaded only if the user us authenticated
     *
     * @return null
     */
    protected function bootApplication()
    {
        if (Innoclapps::isAppInstalled()) {
            Innoclapps::resourcesIn(app_path('Resources'));
            Innoclapps::notificationsIn(app_path('Notifications'));
            Workflows::triggersIn(app_path('Workflows/Triggers'));
            Workflows::registerEventOnlyTriggersListeners();
        }

        View::composer(
            ['app', 'layouts/skin', 'layouts/auth'],
            'App\Http\View\Composers\AppComposer'
        );
    }
}
