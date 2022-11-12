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

namespace App\Http\View\Composers;

use App\Enums\TaxType;
use App\Models\Billable;
use Illuminate\View\View;
use App\Models\ActivityType;
use App\Models\EmailAccount;
use App\Models\BillableProduct;
use App\Innoclapps\Facades\Menu;
use App\Innoclapps\Facades\VoIP;
use App\Innoclapps\Facades\Fields;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Innoclapps\Facades\ReCaptcha;
use App\Innoclapps\Facades\Innoclapps;
use App\Installer\RequirementsChecker;
use App\Support\Highlights\Highlights;
use App\Http\Resources\PipelineResource;
use App\Innoclapps\MailClient\FolderType;
use App\Innoclapps\Settings\SettingsMenu;
use App\Criteria\UserOrderedModelCriteria;
use App\Http\Resources\LostReasonResource;
use App\Http\Resources\CallOutcomeResource;
use App\Innoclapps\Translation\Translation;
use App\Http\Resources\ActivityTypeResource;
use App\Innoclapps\MailClient\ClientManager;
use App\Innoclapps\MailClient\ConnectionType;
use App\Contracts\Repositories\UserRepository;
use App\Criteria\Deal\VisiblePipelinesCriteria;
use App\Contracts\Repositories\PipelineRepository;
use App\Contracts\Repositories\LostReasonRepository;
use App\Contracts\Repositories\CallOutcomeRepository;
use App\Contracts\Repositories\ActivityTypeRepository;

class AppComposer
{
    /**
     * Create a new profile composer.
     *
     * @return void
     */
    public function __construct(
        protected UserRepository $users,
        protected CallOutcomeRepository $callOutcomes,
        protected PipelineRepository $pipelines,
        protected ActivityTypeRepository $activityTypes,
        protected LostReasonRepository $lostReasons,
    ) {
    }

    /**
     * Bind data to the view.
     *
     * @param \Illuminate\View\View $view
     *
     * @return void
     */
    public function compose(View $view)
    {
        Innoclapps::boot();

        // Public config
        $config                     = [];
        $config['apiURL']           = url(\App\Innoclapps\Application::API_PREFIX);
        $config['url']              = rtrim(config('app.url'), '/');
        $config['privacyPolicyUrl'] = privacy_url();
        $config['is_secure']        = request()->secure();
        $config['locale']           = app()->getLocale();
        $config['locales']          = Translation::availableLocales();
        $config['fallback_locale']  = config('app.fallback_locale');
        $config['timezone']         = config('app.timezone');
        $config['max_upload_size']  = config('mediable.max_size');

        $config['reCaptcha'] = [
            'configured' => ReCaptcha::configured(),
            'validate'   => ReCaptcha::shouldShow(),
            'siteKey'    => ReCaptcha::getSiteKey(),
        ];

        // Required in FormField Group for externals forms e.q. web form
        $config['fields'] = [
            'views' => [
                'update' => Fields::UPDATE_VIEW,
                'create' => Fields::CREATE_VIEW,
                'detail' => Fields::DETAIL_VIEW,
            ],
            'height' => [
                'contact' => settings('contact_fields_height'),
                'deal'    => settings('deal_fields_height'),
                'company' => settings('company_fields_height'),
            ],
        ];

        $config['broadcasting'] = [
            'default'    => config('broadcasting.default'),
            'connection' => config('broadcasting.connections.' . config('broadcasting.default')),
        ];

        // Add the needed settings for the application
        // Sensitive settings are not included in this list
        $config['options'] = [
            'time_format'             => config('innoclapps.time_format'),
            'date_format'             => config('innoclapps.date_format'),
            'company_name'            => config('app.name'),
            'logo_light'              => config('app.logo.light'),
            'logo_dark'               => config('app.logo.dark'),
            'first_day_of_week'       => settings('first_day_of_week'),
            'disable_password_forgot' => forgot_password_is_disabled(),
            'allow_lost_reason_enter' => settings('allow_lost_reason_enter'),
            'tax_type'                => Billable::defaultTaxType()?->name,
            'tax_label'               => BillableProduct::defaultTaxLabel(),
            'tax_rate'                => BillableProduct::defaultTaxRate(),
            'discount_type'           => BillableProduct::defaultDiscountType(),
        ];

        $config['date_formats'] = config('app.date_formats');
        $config['time_formats'] = config('app.time_formats');

        $config['currency'] = array_merge(
            array_values(currency(Innoclapps::currency())->toArray())[0],
            ['iso_code' => Innoclapps::currency()]
        );

        $config['taxes'] = [
            'types' => TaxType::names(),
        ];

        // Authenticated user config
        if (Auth::check()) {
            $config['user_id'] = Auth::id();

            if (Auth::user()->isSuperAdmin()) {
                $config['purchase_key'] = config('app.purchase_key');
            }

            $config['invitation'] = [
                'expires_after' => config('app.invitation.expires_after'),
            ];

            $config['soft_deletes'] = [
                'prune_after' => config('innoclapps.soft_deletes.prune_after'),
            ];

            $config['resources'] = Innoclapps::registeredResources()->mapWithKeys(function ($resource) {
                return [$resource->name() => $resource->jsonSerialize()];
            });

            $config['microsoft'] = [
                'client_id' => config('innoclapps.microsoft.client_id'),
            ];

            $config['google'] = [
                'client_id' => config('innoclapps.google.client_id'),
            ];

            $config['voip'] = [
                'client'    => config('innoclapps.voip.client'),
                'endpoints' => [
                    'call'   => VoIP::callUrl(),
                    'events' => VoIP::eventsUrl(),
                ],
            ];

            $config['defaults'] = config('app.defaults');

            $config['favourite_colors'] = Innoclapps::favouriteColors();

            $requirements = new RequirementsChecker;

            $config['requirements'] = [
                'imap' => $requirements->passes('imap'),
                'zip'  => $requirements->passes('zip'),
            ];

            $config['settings'] = [
                'menu' => SettingsMenu::all(),
            ];

            $config['mail'] = [
                'reply_prefix'   => config('innoclapps.mail_client.reply_prefix'),
                'forward_prefix' => config('innoclapps.mail_client.forward_prefix'),
                'accounts'       => [
                    'connections' => ConnectionType::cases(),
                    'encryptions' => ClientManager::ENCRYPTION_TYPES,
                    'from_name'   => EmailAccount::DEFAULT_FROM_NAME_HEADER,
                ],
                'folders' => [
                    'outgoing' => FolderType::outgoingTypes(),
                    'incoming' => FolderType::incomingTypes(),
                    'other'    => FolderType::OTHER,
                    'drafts'   => FolderType::DRAFTS,
                ],
            ];

            $config['associations'] = [
                'common' => Innoclapps::getResourcesNames(),
            ];

            $config['fields'] = array_merge($config['fields'], [
                'optionables'          => Fields::getOptionableFieldsTypes(),
                'custom_fields_types'  => Fields::customFieldsTypes(),
                'custom_field_prefix'  => config('fields.custom_fields.prefix'),
                'groups'               => collect(Innoclapps::resourcesWithFields())->mapWithKeys(
                    fn ($resourceName) => [$resourceName => $resourceName]
                )->all(),
            ]);

            if (! Innoclapps::migrationNeeded()) {
                $config['menu'] = Menu::get();

                $config['highlights'] = Highlights::get();

                $config['notifications_information'] = Innoclapps::notificationsInformation();

                $config['users'] = UserResource::collection($this->users->withResponseRelations()->all());

                $config['deals'] = [
                    'pipelines' => PipelineResource::collection(
                        $this->pipelines->withResponseRelations()
                            ->pushCriteria(VisiblePipelinesCriteria::class)
                            ->pushCriteria(UserOrderedModelCriteria::class)
                            ->all()
                    ),
                    'lost_reasons' => LostReasonResource::collection(
                        $this->lostReasons->withResponseRelations()->orderBy('name')->all()
                    ),
                ];

                $config['activities'] = [
                    'default_activity_type_id' => ActivityType::getDefaultType(),

                    'types' => ActivityTypeResource::collection(
                        $this->activityTypes->withResponseRelations()->orderBy('name')->all()
                    ),
                ];

                $config['calls'] = [
                    'outcomes' => CallOutcomeResource::collection($this->callOutcomes->orderBy('name')->all()),
                ];
            }
        }

        $view->with('config', array_merge($config, Innoclapps::getDataProvidedToScript()));
        $view->with('lang', get_generated_lang(app()->getLocale()));
    }
}
