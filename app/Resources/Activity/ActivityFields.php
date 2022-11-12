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

use App\Models\Activity;
use App\Models\ActivityType;
use App\Innoclapps\Date\Carbon;
use App\Innoclapps\Fields\Text;
use App\Innoclapps\Fields\User;
use Illuminate\Validation\Rule;
use App\Support\Fields\Reminder;
use App\Innoclapps\Fields\Editor;
use App\Innoclapps\Fields\DateTime;
use App\Http\Resources\UserResource;
use App\Innoclapps\Fields\BelongsTo;
use App\Resources\Deal\Fields\Deals;
use App\Support\Fields\GuestsSelect;
use App\Resources\Contact\Fields\Contacts;
use App\Resources\Company\Fields\Companies;
use App\Innoclapps\Fields\IntroductionField;
use App\Contracts\Repositories\UserRepository;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Resources\Activity\Fields\ActivityDueDate;
use App\Resources\Activity\Fields\ActivityEndDate;
use App\Resources\Activity\Fields\ActivityType as ActivityTypeField;

class ActivityFields
{
    /**
    * Provides the activity resource available fields
    *
    * @param \App\Innoclapps\Resources\Resource $resource
    * @param \Illuminate\Http\Request $request
    *
    * @return array
    */
    public function __invoke($resource, $request)
    {
        return [
            Text::make('title', __('activity.title'))
                ->primary()
                ->withMeta(['attributes' => ['placeholder' => __('activity.title')]])
                ->tapIndexColumn(fn ($column) => $column->width('400px')->minWidth('340px'))
                ->creationRules(['required', 'string'])
                ->updateRules(['filled', 'string'])
                ->rules('max:191')
                ->required(true),

            ActivityTypeField::make()
                ->primary()
                ->rules('filled')
                ->required(is_null(ActivityType::getDefaultType()))
                ->creationRules(Rule::requiredIf(is_null(ActivityType::getDefaultType()))),

            ActivityDueDate::make(__('activity.due_date'))
                ->tapIndexColumn(fn ($column) => $column->queryWhenHidden()) // for row class
                ->colClass('col-span-12 sm:col-span-6')
                ->rules('required_with:due_time')
                ->creationRules('required')
                ->required(true)
                ->updateRules(['required_with:end_date', 'required_with:end_time', 'filled']),

            ActivityEndDate::make(__('activity.end_date'))
                ->rules(['required_with:end_time', 'filled'])
                ->updateRules(['required_with:due_date', 'required_with:due_time'])
                ->colClass('col-span-12 sm:col-span-6')
                ->hideFromIndex(),

            Reminder::make('reminder_minutes_before', __('activity.reminder') . ($request->isZapier() ? ' (minutes before due)' : ''))
                ->withDefaultValue(30)
                ->help($resource->model?->isReminded ? __('activity.reminder_update_info') : null)
                ->strictlyForForms()
                // Max is 40320 minutes, 4 weeks, as Google events max is 4 weeks
                ->rules('regex:/^[0-9]+$|(\d{4})-(\d{1,2})-(\d{1,2})\s(\d{1,2}):/', 'not_in:0', 'max:40320')
                ->provideSampleValueUsing(fn () => config('app.defaults.reminder_minutes'))
                ->importUsing(function ($value, $row, $original, $field) {
                    // NOTE: The reminder field must be always after the due date because
                    // we use the due date to create \Carbon\Carbon instance
                    // We will check if the actual reminder field is provided as date
                    // if it's date, we will convert the difference between the due date and the
                    // provided date to determine the actual minutes
                    // Matches: Y-m-d H:
                    if (! is_null($value) && preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})\s(\d{1,2}):/', $value)) {
                        $reminderAt = Carbon::parse($value);
                        $dueDate = Carbon::parse($row['due_date'] . ($row['due_time'] ? ' ' . $row['due_time'] : ''));
                        $value = $reminderAt->isPast() ? null : $dueDate->diffInMinutes($reminderAt);
                    }

                    return [$field->attribute => $value];
                })
                ->cancelable(),

            User::make(__('activity.owner'))
                ->primary()
                ->tapIndexColumn(fn ($column) => $column->primary(false))
                ->setJsonResource(UserResource::class)
                ->withMeta(['attributes' => ['clearable' => false]])
                ->creationRules('required')
                ->updateRules('filled')
                ->required(true)
                ->notification(\App\Notifications\UserAssignedToActivity::class)
                ->trackChangeDate('owner_assigned_date'),

            GuestsSelect::make('guests', __('activity.guests'))
                ->excludeFromImport()
                ->withMeta(['activity' => value(function () use ($resource) {
                    return $resource->model ? $resource->createJsonResource(
                        $resource->model->loadMissing(['guests', 'guests.guestable'])
                    ) : null;
                })])
                ->toggleable()
                ->strictlyForForms()
                ->excludeFromExport()
                ->rules('nullable', 'array'),

                Editor::make('description', __('activity.description'))
                    ->help(__('activity.description_info'))
                    ->rules(['nullable', 'string'])
                    ->helpDisplay('text')
                    ->toggleable()
                    ->strictlyForForms(),

                DateTime::make('owner_assigned_date', __('activity.owner_assigned_date'))
                    ->strictlyForIndex()
                    ->excludeFromImport()
                    ->hidden(),

                Editor::make('note', __('activity.note'))
                    ->withMeta([
                        'attributes' => [
                            'with-mention' => true,
                        ],
                    ])
                    ->help(__('activity.note_info'))
                    ->helpDisplay('text')
                    ->hideFromIndex()
                    ->rules(['nullable', 'string'])
                    ->tapIndexColumn(fn ($column) => $column->asHtml()),

                    BelongsTo::make('creator', UserRepository::class, __('app.created_by'))
                        ->excludeFromImport()
                        ->strictlyForIndex(),

                    IntroductionField::make(__('resource.associate_with_records'))
                        ->excludeFromUpdate(fn () => ! app(ResourceRequest::class)->viaResource())
                        ->excludeFromCreate(fn () => ! app(ResourceRequest::class)->viaResource())
                        ->titleIcon('Link'),

                    Contacts::make()
                        ->hideFromIndex()
                        ->exceptOnForms(fn () => ! app(ResourceRequest::class)->viaResource())
                        ->excludeFromSettings(),

                    Companies::make()
                        ->hideFromIndex()
                        ->exceptOnForms(fn () => ! app(ResourceRequest::class)->viaResource())
                        ->excludeFromSettings(),

                    Deals::make()
                        ->hideFromIndex()
                        ->exceptOnForms(fn () => ! app(ResourceRequest::class)->viaResource())
                        ->excludeFromSettings(),

                    DateTime::make('reminded_at', __('activity.reminder_sent_date'))
                        ->strictlyForIndex()
                        ->excludeFromImport()
                        ->hidden(),

                    DateTime::make('completed_at', __('activity.completed_at'))
                        ->tapIndexColumn(fn ($column) => $column->queryWhenHidden())
                        ->strictlyForIndex()
                        ->excludeFromImport()
                        ->hidden(),

                    DateTime::make('updated_at', __('app.updated_at'))
                        ->excludeFromImportSample()
                        ->strictlyForIndex()
                        ->hidden(),

                    DateTime::make('created_at', __('app.created_at'))
                        ->excludeFromImportSample()
                        ->strictlyForIndex(),
                ];
    }
}
