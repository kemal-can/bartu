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

namespace App\Workflows\Actions;

use App\Innoclapps\Fields\Text;
use App\Innoclapps\Fields\Editor;
use App\Innoclapps\Fields\Select;
use App\Innoclapps\Workflow\Action;
use App\Contracts\Repositories\UserRepository;
use App\Contracts\Repositories\ActivityRepository;
use App\Contracts\Repositories\ActivityTypeRepository;

class CreateActivityAction extends Action
{
    /**
     * Indicates whether to add dynamic assignees in the assigned options
     *
     * @var boolean
     */
    protected bool $withDynamicUsers = true;

    /**
     * @var \App\Contracts\Repositories\ActivityRepository
     */
    protected ActivityRepository $repository;

    /**
     * @var \App\Contracts\Repositories\ActivityTypeRepository
     */
    protected ActivityTypeRepository $activityTypes;

    /**
     * @var \App\Contracts\Repositories\UserRepository
     */
    protected UserRepository $users;

    /**
     * Initialize CreateActivityAction
     */
    public function __construct()
    {
        $this->repository    = resolve(ActivityRepository::class);
        $this->activityTypes = resolve(ActivityTypeRepository::class);
        $this->users         = resolve(UserRepository::class);
    }

    /**
    * Action name
    *
    * @return string
    */
    public static function name() : string
    {
        return __('activity.workflows.actions.create');
    }

    /**
     * Run the trigger
     *
     * @return \App\Models\Activity
     */
    public function run()
    {
        return tap($this->createActivity(), function ($activity) {
            if ($this->viaModelTrigger()) {
                $this->repository->sync(
                    $activity->id,
                    $this->resource->associateableName(),
                    $this->model->id
                );
            }
        });
    }

    /**
    * Action available fields
    *
    * @return array
    */
    public function fields() : array
    {
        return [
            $this->getDueDateField(),
            $this->getUserField(),
            $this->getActivityTypeField(),

           Text::make('activity_title')->withMeta([
                'attributes' => [
                    'placeholder' => __('activity.workflows.fields.create.title'),
                ],
            ])->rules('required'),

            Editor::make('note')->withMeta([
                'attributes' => [
                    'placeholder' => __('activity.workflows.fields.create.note'),
                    'with-image'  => false,
                ],
            ]),
        ];
    }

    /**
     * Get the dynamic users
     *
     * @return array
     */
    protected function getDynamicUsers()
    {
        return $this->withDynamicUsers === false ? [] : [
            [
                'value' => 'owner',
                'label' => __('workflow.fields.for_owner'),
            ],
        ];
    }

    /**
     * Create the activity for the action
     *
     * @return \App\Models\Activity
     */
    protected function createActivity()
    {
        return $this->repository->unguarded(function ($repository) {
            $dueDate = $this->getDueDate();

            return $repository->create([
                'title'                   => $this->activity_title,
                'note'                    => $this->note,
                'activity_type_id'        => $this->activity_type_id,
                'user_id'                 => $this->getOwner(),
                'due_date'                => $dueDate->format('Y-m-d'),
                'due_time'                => $this->due_date === 'now' ? $dueDate->format('H:i') . ':00' : null,
                'end_date'                => $dueDate->format('Y-m-d'),
                'reminder_minutes_before' => config('app.defaults.reminder_minutes'),
                'created_by'              => $this->workflow->created_by,
                 // We will add few seconds to ensure that it's properly sorted in the activity tabs
                 // and the created activity is always at the bottom
                'created_at' => now()->addSecond(3),
            ]);
        });
    }

    /**
     * Add dynamic users incude flag
     *
     * @param boolean $value
     *
     * @return static
     */
    public function withoutDynamicUsers(bool $value = true) : static
    {
        $this->withDynamicUsers = $value === false;

        return $this;
    }

    /**
     * Get the new activity owner
     *
     * @return int
     */
    protected function getOwner() : int
    {
        return match ($this->user_id) {
            'owner' => $this->model->user_id,
            default => $this->user_id,
        };
    }

    /**
     * Get the new activity due date
     *
     * @return \App\Innoclapps\Date\Carbon
     */
    protected function getDueDate()
    {
        $now = now();

        return match ($this->due_date) {
            'in_1_day' => $now->addDays(1),
            'in_2_day' => $now->addDays(2),
            'in_3_day' => $now->addDays(3),
            'in_4_day' => $now->addDays(4),
            'in_5_day' => $now->addDays(5),
            default    => $now,
        };
    }

    /**
     * Get the user field
     *
     * @return \App\Innoclapps\Fields\Select
     */
    protected function getUserField()
    {
        return Select::make('user_id')->options(function () {
            return collect($this->getDynamicUsers())
                ->merge($this->users->all()->map(function ($user) {
                    return [
                        'value' => $user->id,
                        'label' => $user->name,
                    ];
                }));
        })->withDefaultValue('owner')
            ->rules('required');
    }

    /**
     * Get the activity type field
     *
     * @return \App\Innoclapps\Fields\Select
     */
    protected function getActivityTypeField()
    {
        return Select::make('activity_type_id')->options(function () {
            return $this->activityTypes->orderBy('name')->all()
                ->map(fn ($type) => [
                    'value' => $type->id,
                    'label' => $type->name,
                ])->all();
        })->label(null)
            ->withDefaultValue(
                fn () => $this->activityTypes->findByFlag('task')->id
            )->rules('required');
    }

    /**
     * Get the due date field
     *
     * @return \App\Innoclapps\Fields\Select
     */
    protected function getDueDateField()
    {
        return Select::make('due_date')->options([
                'now'       => __('workflow.fields.dates.now'),
                'in_1_day'  => __('workflow.fields.dates.in_1_day'),
                'in_2_days' => __('workflow.fields.dates.in_2_days'),
                'in_3_days' => __('workflow.fields.dates.in_3_days'),
                'in_4_days' => __('workflow.fields.dates.in_4_days'),
                'in_5_days' => __('workflow.fields.dates.in_5_days'),
            ])
            ->withDefaultValue('now')
            ->withMeta(['attributes' => ['clearable' => false, 'placeholder' => __('workflow.fields.dates.due_at')]])
            ->rules('required');
    }
}
