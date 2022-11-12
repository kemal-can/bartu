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

namespace App\Resources\Deal;

use Illuminate\Http\Request;
use App\Innoclapps\Table\Table;
use App\Contracts\BillableResource;
use App\Http\Resources\DealResource;
use App\Innoclapps\Resources\Import;
use App\Innoclapps\Resources\Resource;
use App\Criteria\Deal\OwnDealsCriteria;
use App\Resources\Actions\DeleteAction;
use App\Innoclapps\Menu\Item as MenuItem;
use App\Criteria\UserOrderedModelCriteria;
use App\Resources\Deal\Filters\DealStatus;
use App\Http\View\FrontendComposers\Template;
use App\Innoclapps\Settings\SettingsMenuItem;
use App\Contracts\Repositories\DealRepository;
use App\Criteria\Deal\DealsByPipelineCriteria;
use App\Innoclapps\Filters\Date as DateFilter;
use App\Innoclapps\Filters\Text as TextFilter;
use App\Resources\Deal\Frontend\ViewComponent;
use App\Contracts\Repositories\StageRepository;
use App\Criteria\Deal\VisiblePipelinesCriteria;
use App\Support\Filters\BillableProductsFilter;
use App\Support\Filters\ResourceUserTeamFilter;
use App\Innoclapps\Contracts\Resources\Mediable;
use App\Contracts\Repositories\WebFormRepository;
use App\Innoclapps\Contracts\Resources\Tableable;
use App\Contracts\Repositories\PipelineRepository;
use App\Innoclapps\Contracts\Resources\Exportable;
use App\Innoclapps\Contracts\Resources\Importable;
use App\Innoclapps\Filters\Select as SelectFilter;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Resources\User\Filters\User as UserFilter;
use App\Innoclapps\Contracts\Resources\Resourceful;
use App\Innoclapps\Filters\Numeric as NumericFilter;
use App\Resources\Inbox\Filters\ResourceEmailsFilter;
use App\Innoclapps\Filters\DateTime as DateTimeFilter;
use App\Innoclapps\Contracts\Resources\AcceptsCustomFields;
use App\Innoclapps\Filters\MultiSelect as MultiSelectFilter;
use App\Resources\Activity\Filters\ResourceActivitiesFilter;
use App\Resources\Activity\Filters\ResourceNextActivityDate as ResourceNextActivityDateFilter;

class Deal extends Resource implements Resourceful, Tableable, Mediable, Importable, Exportable, BillableResource, AcceptsCustomFields
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
        $repository = resolve(DealRepository::class);

        if (request()->isSearching() && request()->has('pipeline_id')) {
            $repository->pushCriteria(new DealsByPipelineCriteria((int) request()->pipeline_id));
        }

        return $repository;
    }

    /**
     * Get the menu items for the resource
     *
     * @return array
     */
    public function menu() : array
    {
        return [
            MenuItem::make(static::label(), '/deals', 'Folder')
                ->position(5)
                ->inQuickCreate()
                ->keyboardShortcutChar('D'),
        ];
    }

    /**
     * Get the resource relationship name when it's associated
     *
     * @return string
     */
    public function associateableName() : string
    {
        return 'deals';
    }

    /**
     * Get the resource available cards
     *
     * @return array
     */
    public function cards() : array
    {
        return [
            (new Cards\ClosingDeals)->onlyOnDashboard()
                ->withUserSelection(function () {
                    return auth()->user()->can('view all deals') ? auth()->id() : false;
                })
                ->help(__('deal.cards.closing_info')),

            (new Cards\DealsByStage)->refreshOnActionExecuted()
                ->help(__('app.cards.creation_date_info')),

            (new Cards\DealsLostInStage)->color('danger')
                ->onlyOnDashboard(),

            (new Cards\DealsWonInStage)->color('success')
                ->onlyOnDashboard(),

            (new Cards\WonDealsByDay)->refreshOnActionExecuted()->withUserSelection(function () {
                return auth()->user()->can('view all deals');
            })->color('success'),

            (new Cards\WonDealsByMonth)->withUserSelection(function () {
                return auth()->user()->can('view all deals');
            })->color('success')->onlyOnDashboard(),

            (new Cards\RecentlyCreatedDeals)->onlyOnDashboard(),

            (new Cards\RecentlyModifiedDeals)->onlyOnDashboard(),

            (new Cards\WonDealsRevenueByMonth)->color('success')
                ->canSeeWhen('is-super-admin')
                ->onlyOnDashboard(),

            (new Cards\CreatedDealsBySaleAgent)->canSeeWhen('is-super-admin')
                ->onlyOnDashboard(),

            (new Cards\AssignedDealsBySaleAgent)->canSeeWhen('is-super-admin')
                ->onlyOnDashboard(),
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
        return new DealTable($repository, $request);
    }

    /**
     * Get the json resource that should be used for json response
     *
     * @return string
     */
    public function jsonResource() : string
    {
        return DealResource::class;
    }

    /**
     * Get the criteria that should be used to fetch only own data for the user
     *
     * @return string
     */
    public function ownCriteria() : string
    {
        return OwnDealsCriteria::class;
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
        return (new DealFields)($this);
    }

    /**
     * Get the resource importable class
     *
     * @return \App\Innoclapps\Resources\Import
     */
    public function importable() : Import
    {
        return new DealImport($this);
    }

    /**
     * Get the resource rules available for create and update
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [
            'lost_reason' => 'sometimes|nullable|string|max:191',
        ];
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
            TextFilter::make('name', __('fields.deals.name'))->withoutEmptyOperators(),

            NumericFilter::make('amount', __('fields.deals.amount')),

            DateFilter::make('expected_close_date', __('fields.deals.expected_close_date')),

            SelectFilter::make('pipeline_id', __('fields.deals.pipeline.name'))
                ->labelKey('name')
                ->valueKey('id')
                ->options(function () {
                    return app(PipelineRepository::class)
                        ->columns(['id', 'name'])
                        ->pushCriteria(VisiblePipelinesCriteria::class)
                        ->pushCriteria(UserOrderedModelCriteria::class)
                        ->all();
                }),

            MultiSelectFilter::make('stage_id', __('fields.deals.stage.name'))
                ->labelKey('name')
                ->valueKey('id')
                ->options(function () use ($request) {
                    return app(StageRepository::class)->allStagesForOptions($request->user());
                }),

            DateTimeFilter::make('stage_changed_date', __('deal.stage.changed_date')),

            DealStatus::make(),

            TextFilter::make('lost_reason', __('deal.lost_reasons.lost_reason'))
                ->withNullOperators()
                ->withoutEmptyOperators(),

            UserFilter::make(__('fields.deals.user.name')),

            ResourceUserTeamFilter::make(__('team.owner_team')),

            DateTimeFilter::make('owner_assigned_date', __('fields.deals.owner_assigned_date')),

            BillableProductsFilter::make(),

            ResourceActivitiesFilter::make(),

            ResourceEmailsFilter::make(),

            SelectFilter::make('web_form_id', __('form.form'))
                ->labelKey('title')
                ->valueKey('id')
                ->withNullOperators()
                ->options(function () {
                    return app(WebFormRepository::class)->columns(['id', 'title'])->all();
                })->canSeeWhen('is-super-admin'),

            ResourceNextActivityDateFilter::make(),

            UserFilter::make(__('app.created_by'), 'created_by')->canSeeWhen('view all deals'),

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
            (new \App\Resources\Actions\AssignOwnerAction)->onlyOnIndex(),
            new Actions\ChangeDealStage,
            (new Actions\MarkAsWon)->withoutConfirmation(),
            new Actions\MarkAsLost,
            (new Actions\MarkAsOpen)->withoutConfirmation(),

            (new DeleteAction)->useName(__('app.delete'))
                ->useRepository(static::repository()),

            (new DeleteAction)->isBulk()
                ->useName(__('app.delete'))
                ->useRepository(static::repository())
                ->authorizedToRunWhen(
                    fn ($request, $model) => $request->user()->can('bulk delete deals')
                ),
        ];
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label() : string
    {
        return __('deal.deals');
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel() : string
    {
        return __('deal.deal');
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
            SettingsMenuItem::make(__('deal.deals'), '/settings/deals', 'Folder')->order(22),
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
