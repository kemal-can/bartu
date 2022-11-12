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

namespace App\Resources\Activity;

use Illuminate\Http\Request;
use App\Innoclapps\Table\Table;
use App\Innoclapps\Resources\Resource;
use App\Innoclapps\Facades\Permissions;
use App\Innoclapps\QueryBuilder\Parser;
use App\Resources\Actions\DeleteAction;
use App\Http\Resources\ActivityResource;
use App\Innoclapps\Menu\Item as MenuItem;
use App\Innoclapps\Settings\SettingsMenuItem;
use App\Innoclapps\Filters\Text as TextFilter;
use App\Criteria\Activity\OwnActivitiesCriteria;
use App\Innoclapps\Contracts\Resources\Mediable;
use App\Innoclapps\Filters\Radio as RadioFilter;
use App\Innoclapps\Models\PinnedTimelineSubject;
use App\Innoclapps\Contracts\Resources\Tableable;
use App\Contracts\Repositories\ActivityRepository;
use App\Innoclapps\Contracts\Resources\Exportable;
use App\Innoclapps\Contracts\Resources\Importable;
use App\Innoclapps\Filters\Select as SelectFilter;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Resources\Activity\Filters\OpenActivities;
use App\Resources\User\Filters\User as UserFilter;
use App\Innoclapps\Contracts\Resources\Resourceful;
use App\Resources\Activity\Filters\OverdueActivities;
use App\Contracts\Repositories\ActivityTypeRepository;
use App\Innoclapps\Filters\DateTime as DateTimeFilter;
use App\Resources\Activity\Filters\DueTodayActivities;
use App\Resources\Activity\Filters\DueThisWeekActivities;
use App\Innoclapps\Criteria\WithPinnedTimelineSubjectsCriteria;

class Activity extends Resource implements Resourceful, Tableable, Mediable, Importable, Exportable
{
    /**
    * The column the records should be default ordered by when retrieving
    *
    * @var string
    */
    public static string $orderBy = 'title';

    /**
    * Indicates whether the resource is globally searchable
    *
    * @var boolean
    */
    public static bool $globallySearchable = true;

    /**
    * Get the underlying resource repository
    *
    * @return \App\Innoclapps\Repository\AppRepository
    */
    public static function repository()
    {
        return resolve(ActivityRepository::class);
    }

    /**
    * Get the menu items for the resource
    *
    * @return array
    */
    public function menu() : array
    {
        return [
            MenuItem::make(static::label(), '/activities', 'Calendar')
                ->position(10)
                ->inQuickCreate()
                ->keyboardShortcutChar('A'),
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
        if ($request->filled('activity_type_id') && is_numeric($request->activity_type_id)) {
            $repository->scopeQuery(
                fn ($query) => $query->where('activity_type_id', (int) $request->activity_type_id)
            );
        }

        return new ActivityTable($repository, $request);
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
        return (new ActivityFields)($this, $request);
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
            TextFilter::make('title', __('activity.title'))->withoutEmptyOperators(),

            UserFilter::make(__('activity.owner'))->withNullOperators(false),

            DateTimeFilter::make('owner_assigned_date', __('activity.owner_assigned_date')),

            SelectFilter::make('activity_type_id', __('activity.type.type'))
                ->valueKey('id')
                ->labelKey('name')
                ->options(function () {
                    return resolve(ActivityTypeRepository::class)->columns(['id', 'name'])->all();
                }),

            RadioFilter::make('is_completed', __('activity.is_completed'))->options([
                true  => __('app.yes'),
                false => __('app.no'),
                ])->query(function ($builder, $value, $condition) {
                    $method = $value ? 'completed' : 'incomplete';

                    return $builder->{$method}($condition);
                }),

                with(DateTimeFilter::make('due_date', __('activity.due_date')), function ($filter) {
                    return $filter->query($this->dueAndEndDateFilterQueryCallback($filter));
                }),

                with(DateTimeFilter::make('end_date', __('activity.end_date')), function ($filter) {
                    return $filter->query($this->dueAndEndDateFilterQueryCallback($filter));
                }),

                DateTimeFilter::make('reminder_at', __('activity.reminder')),

                UserFilter::make(__('app.created_by'), 'created_by')->canSeeWhen('view all activities'),

                OverdueActivities::make(),

                OpenActivities::make(),

                DueTodayActivities::make(),

                DueThisWeekActivities::make(),

                DateTimeFilter::make('created_at', __('app.created_at')),
            ];
    }

    /**
    * Get the query for the due and end date filter query callback
    *
    * @return callable
    */
    protected function dueAndEndDateFilterQueryCallback($filter)
    {
        return function ($builder, $value, $condition, $sqlOperator, $rule, Parser $parser) use ($filter) {
            $rule->query->rule = static::model()::dueDateQueryExpression();

            return $parser->makeQueryWhenDate($builder, $filter, $rule, $sqlOperator['operator'], $value, $condition);
        };
    }

    /**
    * Get the criteria that should be used to fetch only own data for the user
    *
    * @return string|null
    */
    public function ownCriteria() : string
    {
        return OwnActivitiesCriteria::class;
    }

    /**
    * Get the json resource that should be used for json response
    *
    * @return string
    */
    public function jsonResource() : string
    {
        return ActivityResource::class;
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
                (new Actions\MarkActivityAsComplete)->withoutConfirmation(),
                (new Actions\UpdateActivityType)->onlyOnIndex(),

                (new DeleteAction)->useName(__('app.delete'))
                    ->useRepository(static::repository()),

                (new DeleteAction)->isBulk()
                    ->useName(__('app.delete'))
                    ->useRepository(static::repository())
                    ->authorizedToRunWhen(
                        fn ($request, $model) => $request->user()->can('bulk delete activities')
                    ),
            ];
    }

    /**
    * Get the resource available cards
    *
    * @return array
    */
    public function cards() : array
    {
        return [
                (new Cards\MyActivities)->help(__('activity.cards.my_activities_info')),
                (new Cards\UpcomingUserActivities)->help(__('activity.cards.upcoming_info')),
                (new Cards\ActivitiesCreatedBySaleAgent)->canSeeWhen('view all activities')
                    ->color('success')
                    ->help(__('activity.cards.created_by_agent_info')),
            ];
    }

    /**
    * Get the displayable singular label of the resource.
    *
    * @return string
    */
    public static function singularLabel() : string
    {
        return __('activity.activity');
    }

    /**
    * Get the displayable label of the resource.
    *
    * @return string
    */
    public static function label() : string
    {
        return __('activity.activities');
    }

    /**
    * Get the resource relationship name when it's associated
    *
    * @return string
    */
    public function associateableName() : string
    {
        return 'activities';
    }

    /**
     * Get the countable relations when quering associated records
     *
     * @return array
     */
    public function withCountWhenAssociated() : array
    {
        return ['comments'];
    }

    /**
    * Create query when the resource is associated for index
    *
    * @param \App\Innoclapps\Models\Model $primary
    * @param bool $applyOrder
    *
    * @return \App\Innoclapps\Repositories\AppRepository
    */
    public function associatedIndexQuery($primary, $applyOrder = true)
    {
        return tap(parent::associatedIndexQuery($primary, $applyOrder), function ($repository) {
            [$with, $withCount] = static::getEagerloadableRelations($this->fieldsForIndexQuery());
            // For associations keys to be included in the JSON resource
            $repository->with(
                array_merge(
                    $this->availableAssociations()->map->associateableName()->all(),
                    ['pinnedTimelineSubjects'],
                    $with->all()
                )
            )->withCount($withCount->all())
                ->pushCriteria($this->ownCriteria())
                ->withResponseRelations();
        });
    }

    /**
     * Create the query when the resource is associated and the data is intended for the timeline
     *
     * @param \App\Innoclapps\Models\Model $subject
     *
     * @return \App\Innoclapps\Repositories\AppRepository
     */
    public function timelineQuery($subject)
    {
        return $this->associatedIndexQuery($subject, false)
            ->with('pinnedTimelineSubjects')
            ->pushCriteria(new WithPinnedTimelineSubjectsCriteria($subject))
            // Pinned are always first, then the non-completed sorted by due date asc
            ->orderBy((new PinnedTimelineSubject)->getQualifiedCreatedAtColumn(), 'desc')
            ->orderBy('completed_at', 'asc')
            ->orderBy(static::model()::dueDateQueryExpression(), 'asc');
    }

    /**
    * Register permissions for the resource
    *
    * @return void
    */
    public function registerPermissions() : void
    {
        $this->registerCommonPermissions();

        Permissions::group($this->name(), function ($manager) {
            $manager->register('view', [
                'permissions' => [
                    'view attends and owned activities' => __('activity.permissions.attends_and_owned'),
                ],
            ]);
        });
    }

    /**
     * Register the settings menu items for the resource
     *
     * @return array
     */
    public function settingsMenu() : array
    {
        return [
            SettingsMenuItem::make(__('activity.activities'), '/settings/activities', 'Calendar')->order(21),
        ];
    }
}
