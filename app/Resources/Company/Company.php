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

namespace App\Resources\Company;

use Illuminate\Http\Request;
use App\Innoclapps\Table\Table;
use App\Innoclapps\Filters\Operand;
use App\Innoclapps\Resources\Import;
use App\Innoclapps\Resources\Resource;
use App\Http\Resources\CompanyResource;
use App\Resources\Actions\DeleteAction;
use App\Innoclapps\Menu\Item as MenuItem;
use App\Http\View\FrontendComposers\Template;
use App\Innoclapps\Settings\SettingsMenuItem;
use App\Support\Filters\AddressOperandFilter;
use App\Criteria\Company\OwnCompaniesCriteria;
use App\Innoclapps\Filters\Text as TextFilter;
use App\Support\Filters\ResourceUserTeamFilter;
use App\Innoclapps\Contracts\Resources\HasEmail;
use App\Innoclapps\Contracts\Resources\Mediable;
use App\Contracts\Repositories\CompanyRepository;
use App\Innoclapps\Contracts\Resources\Tableable;
use App\Resources\Company\Frontend\ViewComponent;
use App\Contracts\Repositories\IndustryRepository;
use App\Innoclapps\Contracts\Resources\Exportable;
use App\Innoclapps\Contracts\Resources\Importable;
use App\Innoclapps\Filters\Select as SelectFilter;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Resources\User\Filters\User as UserFilter;
use App\Innoclapps\Contracts\Resources\Resourceful;
use App\Resources\Deal\Filters\ResourceDealsFilter;
use App\Innoclapps\Filters\HasMany as HasManyFilter;
use App\Resources\Inbox\Filters\ResourceEmailsFilter;
use App\Innoclapps\Filters\DateTime as DateTimeFilter;
use App\Resources\Source\Filters\Source as SourceFilter;
use App\Innoclapps\Contracts\Resources\AcceptsCustomFields;
use App\Resources\Activity\Filters\ResourceActivitiesFilter;
use App\Resources\Activity\Filters\ResourceNextActivityDate as ResourceNextActivityDateFilter;

class Company extends Resource implements Resourceful, Tableable, Mediable, Importable, Exportable, HasEmail, AcceptsCustomFields
{
    /**
    * Indicates whether the resource has Zapier hooks
    *
    * @var boolean
    */
    public static bool $hasZapierHooks = true;

    /**
    * The column the records should be default ordered by when retrieving
    *
    * @var string
    */
    public static string $orderBy = 'name';

    /**
    * Indicates whether the resource is globally searchable
    *
    * @var boolean
    */
    public static bool $globallySearchable = true;

    /**
    * Indicates whether the resource fields are customizeable
    *
    * @var boolean
    */
    public static bool $fieldsCustomizable = true;

    /**
    * Get the underlying resource repository
    *
    * @return \App\Innoclapps\Repository\AppRepository
    */
    public static function repository()
    {
        return resolve(CompanyRepository::class);
    }

    /**
    * Get the resource model email address field name
    *
    * @return string
    */
    public function emailAddressField() : string
    {
        return 'email';
    }

    /**
    * Get the menu items for the resource
    *
    * @return array
    */
    public function menu() : array
    {
        return [
            MenuItem::make(static::label(), '/companies', 'OfficeBuilding')
                ->position(25)
                ->inQuickCreate()
                ->keyboardShortcutChar('O'),
        ];
    }

    /**
    * Get the resource relationship name when it's associated
    *
    * @return string
    */
    public function associateableName() : string
    {
        return 'companies';
    }

    /**
    * Get the resource available cards
    *
    * @return array
    */
    public function cards() : array
    {
        return [
            (new Cards\CompaniesByDay)->refreshOnActionExecuted()->help(__('app.cards.creation_date_info')),
            (new Cards\CompaniesBySource)->refreshOnActionExecuted()->help(__('app.cards.creation_date_info'))->color('info'),
        ];
    }

    /**
    * Provide the resource table class
    *
    * @param \App\Innoclapps\Repository\BaseRepository $repository
    * @param \Illuminate\Http\Request $request
    *
    * @return \App\Innoclapps\Table\Table
    */
    public function table($repository, Request $request) : Table
    {
        return new CompanyTable($repository, $request);
    }

    /**
    * Get the json resource that should be used for json response
    *
    * @return string
    */
    public function jsonResource() : string
    {
        return CompanyResource::class;
    }

    /**
    * Get the criteria that should be used to fetch only own data for the user
    *
    * @return string
    */
    public function ownCriteria() : string
    {
        return OwnCompaniesCriteria::class;
    }

    /**
    * Provides the resource available CRUD fields
    *
    * @param \Illuminate\Http\Request $request
    *
    * @return array
    */
    public function fields(Request $request) : array
    {
        return (new CompanyFields)($this, $request);
    }

    /**
    * Get the resource importable class
    *
    * @return \App\Innoclapps\Resources\Import
    */
    public function importable() : Import
    {
        return parent::importable()->validateDuplicatesUsing(function ($request) {
            return $this->lookUpForDuplicateImportCompany($request);
        });
    }

    /**
    * Get the resource available Filters
    *
    * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
    *
    * @return array
    */
    public function filters(ResourceRequest $request) : array
    {
        return [
            TextFilter::make('companies.name', __('fields.companies.name'))->withoutEmptyOperators(),

            TextFilter::make('domain', __('fields.companies.domain')),

            TextFilter::make('email', __('fields.companies.email')),

            UserFilter::make(__('fields.companies.user.name')),

            ResourceUserTeamFilter::make(__('team.owner_team')),

            DateTimeFilter::make('owner_assigned_date', __('fields.companies.owner_assigned_date')),

            ResourceActivitiesFilter::make(),

            SelectFilter::make('industry_id', __('fields.companies.industry.name'))
                ->labelKey('name')
                ->valueKey('id')
                ->options(function () {
                    return resolve(IndustryRepository::class)->get(['id', 'name'])->all();
                }),

            SourceFilter::make(),

            AddressOperandFilter::make('companies'),

            HasManyFilter::make('phones', __('fields.companies.phone'))->setOperands([
                (new Operand('number', __('fields.companies.phone')))->filter(TextFilter::class),
                ])->hideOperands(),

                ResourceDealsFilter::make(__('company.company')),

                ResourceEmailsFilter::make(),

                ResourceNextActivityDateFilter::make(),

                UserFilter::make(__('app.created_by'), 'created_by')->canSeeWhen('view all companies'),

                DateTimeFilter::make('created_at', __('app.created_at')),
            ];
    }

    /**
     * Provides the resource available actions
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return array
     */
    public function actions(ResourceRequest $request) : array
    {
        return [
                new \App\Resources\Actions\SearchInGoogleAction,
                (new \App\Resources\Actions\AssignOwnerAction)->onlyOnIndex(),

                (new DeleteAction)->useName(__('app.delete'))
                    ->useRepository(static::repository()),

                (new DeleteAction)->isBulk()
                    ->useName(__('app.delete'))
                    ->useRepository(static::repository())
                    ->authorizedToRunWhen(
                        fn ($request, $model) => $request->user()->can('bulk delete companies')
                    ),
            ];
    }

    /**
    * Try to find a duplicate company for the import request
    *
    * @param \App\Innoclapps\Resources\Http\ImportRequest $request
    *
    * @return \App\Models\Contact|null
    */
    protected function lookUpForDuplicateImportCompany($request)
    {
        $cursor = once(function () {
            return $this->repository()->cursor();
        });

        // First we will check by address, as it's most logical
        if (! empty($request->street) &&
            ! empty($request->city) &&
            ! empty($request->postal_code) &&
            ! empty($request->country_id)) {
            return $cursor->filter(function ($company) use ($request) {
                return strcasecmp($company->street, $request->street) === 0 &&
                        strcasecmp($company->city, $request->city) === 0 &&
                        strcasecmp($company->postal_code, $request->postal_code) === 0 &&
                    (int) $company->country_id === (int) $request->country_id;
            })->first();
        }

        // Then we will check by email as the company email is unique
        if ($request->has('email') && $request->filled('email')) {
            return $cursor->firstWhere('email', $request->email);
        }
    }

    /**
    * Get the displayable label of the resource.
    *
    * @return string
    */
    public static function label() : string
    {
        return __('company.companies');
    }

    /**
    * Get the displayable singular label of the resource.
    *
    * @return string
    */
    public static function singularLabel() : string
    {
        return __('company.company');
    }

    /**
    * Register permissions for the resource
    *
    * @return void
    */
    public function registerPermissions() : void
    {
        $this->registerCommonPermissions();
    }

    /**
     * Register the settings menu items for the resource
     *
     * @return array
     */
    public function settingsMenu() : array
    {
        return [
            SettingsMenuItem::make(__('product.products'), '/settings/products', 'MenuAlt1')->order(23),
        ];
    }

    /**
     * Get the resource frontend template
     *
     * @return \App\Http\View\FrontendComposers\Template
     */
    public function frontendTemplate() : Template
    {
        return (new Template)->viewComponent(new ViewComponent);
    }

    /**
     * Serialize the resource
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return array_merge(parent::jsonSerialize(), [
            'frontend' => $this->frontendTemplate(),
        ]);
    }
}
