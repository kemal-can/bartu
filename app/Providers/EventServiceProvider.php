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

use App\Events\DealMovedToStage;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\Listeners\RememberUserLocale;
use App\Events\EmailAccountMessageCreated;
use App\Listeners\CreateEmailAccountViaOAuth;
use App\Listeners\LogDealMovedToStageActivity;
use App\Innoclapps\Workflow\WorkflowEventsSubscriber;
use App\Listeners\AttachEmailAccountMessageToContact;
use App\Innoclapps\OAuth\Events\OAuthAccountConnected;
use App\Listeners\CreateContactFromEmailAccountMessage;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
       DealMovedToStage::class => [
            LogDealMovedToStageActivity::class,
        ],
        OAuthAccountConnected::class => [
            CreateEmailAccountViaOAuth::class,
        ],
        EmailAccountMessageCreated::class => [
            CreateContactFromEmailAccountMessage::class,
            AttachEmailAccountMessageToContact::class,
        ],
        Login::class => [
           RememberUserLocale::class,
        ],
        Logout::class => [
            RememberUserLocale::class,
        ],
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
       WorkflowEventsSubscriber::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
