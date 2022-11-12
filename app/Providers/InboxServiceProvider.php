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
use Illuminate\Support\Facades\Auth;
use App\Innoclapps\Facades\Innoclapps;
use App\Innoclapps\Facades\Permissions;
use Illuminate\Support\ServiceProvider;
use App\Innoclapps\Menu\Item as MenuItem;
use App\Repositories\EmailAccountRepositoryEloquent;
use App\Contracts\Repositories\EmailAccountRepository;
use App\Repositories\EmailAccountFolderRepositoryEloquent;
use App\Repositories\EmailAccountMessageRepositoryEloquent;
use App\Contracts\Repositories\EmailAccountFolderRepository;
use App\Contracts\Repositories\EmailAccountMessageRepository;
use App\Repositories\PredefinedMailTemplateRepositoryEloquent;
use App\Contracts\Repositories\PredefinedMailTemplateRepository;

class InboxServiceProvider extends ServiceProvider
{
    /**
    * All of the container bindings that should be registered.
    *
    * @var array
    */
    public $bindings = [
        EmailAccountRepository::class           => EmailAccountRepositoryEloquent::class,
        EmailAccountFolderRepository::class     => EmailAccountFolderRepositoryEloquent::class,
        EmailAccountMessageRepository::class    => EmailAccountMessageRepositoryEloquent::class,
        PredefinedMailTemplateRepository::class => PredefinedMailTemplateRepositoryEloquent::class,
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Innoclapps::booting(function () {
            Menu::register(
                MenuItem::make(__('inbox.inbox'), '/inbox', 'Mail')
                    ->position(15)
                    ->badge(fn () => resolve(EmailAccountRepository::class)->countUnreadMessagesForUser(Auth::user()))
                    ->badgeVariant('info')
            );
        });

        $this->registerPermissions();
    }

    /**
     * Register inbox permissions
     *
     * @return void
     */
    public function registerPermissions() : void
    {
        Permissions::group(['name' => 'inbox', 'as' => __('inbox.shared')], function ($manager) {
            $manager->register('access-inbox', [
                'as'          => __('role.capabilities.access'),
                'permissions' => [
                    'access shared inbox' => __('role.capabilities.access'),
                ],
            ]);
        });
    }
}
