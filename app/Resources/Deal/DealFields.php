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

use App\Enums\DealStatus;
use App\Innoclapps\Date\Carbon;
use App\Innoclapps\Fields\Date;
use App\Innoclapps\Fields\Text;
use App\Innoclapps\Fields\User;
use Illuminate\Validation\Rule;
use App\Innoclapps\Facades\Fields;
use App\Innoclapps\Fields\Numeric;
use App\Support\Fields\ImportNote;
use App\Innoclapps\Fields\DateTime;
use App\Http\Resources\UserResource;
use App\Innoclapps\Fields\MorphToMany;
use App\Resources\Deal\Fields\Pipeline;
use App\Resources\Contact\Fields\Contacts;
use App\Resources\Company\Fields\Companies;
use App\Innoclapps\Fields\IntroductionField;
use App\Resources\Deal\Fields\PipelineStage;
use App\Resources\Deal\Fields\LostReasonField;
use App\Resources\Activity\Fields\NextActivityDate;

class DealFields
{
    /**
    * Provides the deal resource available fields
    *
    * @param \App\Innoclapps\Resources\Resource $resource
    *
    * @return array
    */
    public function __invoke($resource)
    {
        return [
            Text::make('name', __('fields.deals.name'))
                ->primary()
                ->tapIndexColumn(fn ($column) => $column->width('340px')->minWidth('340px'))
                ->creationRules(['required', 'string'])
                ->updateRules(['filled', 'string'])
                ->rules('max:191')
                ->excludeFromDetail()
                ->excludeFromSettings(Fields::DETAIL_VIEW)
                ->required(true),

            $pipeline = Pipeline::make()->primary()
                ->withMeta(['attributes' => ['clearable' => false]])
                ->rules('filled')
                ->required(true)
                ->tapIndexColumn(fn ($column) => $column->primary(false))
                ->excludeFromImport()
                ->excludeFromDetail()
                ->excludeFromUpdate()
                ->hideFromIndex()
                ->excludeFromSettings()
                ->showValueWhenUnauthorizedToView(),

            PipelineStage::make()->primary()
                ->withMeta(['attributes' => ['clearable' => false]])
                ->dependsOn($pipeline, 'stages')
                ->tapIndexColumn(fn ($column) => $column->primary(false))
                ->excludeFromDetail()
                ->excludeFromUpdate()
                ->excludeFromSettings()
                ->showValueWhenUnauthorizedToView(),

            Numeric::make('amount', __('fields.deals.amount'))
                ->readOnly(
                    fn () => $resource->model?->products->isNotEmpty() ?? false
                )
                ->primary()
                ->currency()
                ->tapIndexColumn(fn ($column) => $column->primary(false)),

                Date::make('expected_close_date', __('fields.deals.expected_close_date'))
                    ->primary()
                    ->clearable()
                    ->withDefaultValue(Carbon::parse()->endOfMonth()->format('Y-m-d'))
                    ->tapIndexColumn(fn ($column) => $column->primary(false)),

                Text::make('status', __('deal.status.status'))
                    ->exceptOnForms()
                    ->excludeFromImport()
                    ->rules(['sometimes', 'nullable', 'string', Rule::in(DealStatus::names())])
                    ->showValueWhenUnauthorizedToView()
                    ->resolveUsing(fn ($model) => $model->status->name)
                    ->displayUsing(fn ($model, $value) => __('deal.status.' . $value)) // For mail placeholder
                    ->tapIndexColumn(function ($column) {
                        $column->centered()->displayAs(fn ($model) => $model->status->name)
                            ->withMeta(['badgeVariants' => DealStatus::badgeVariants()]);
                    }),

                LostReasonField::make('lost_reason', __('deal.lost_reasons.lost_reason'))
                    ->strictlyForIndex()
                    ->excludeFromImportSample()
                    ->hidden(),

                User::make(__('fields.deals.user.name'))
                    ->primary()
                    ->withMeta(['attributes' => ['placeholder' => __('app.no_owner')]])
                    ->setJsonResource(UserResource::class)
                    ->notification(\App\Notifications\UserAssignedToDeal::class)
                    ->trackChangeDate('owner_assigned_date')
                    ->tapIndexColumn(fn ($column) => $column->primary(false))
                    ->excludeFromDetail()
                    ->excludeFromSettings(Fields::DETAIL_VIEW)
                    ->showValueWhenUnauthorizedToView(),

                IntroductionField::make(__('resource.associate_with_records'))
                    ->strictlyForCreation()
                    ->titleIcon('Link')
                    ->order(1000),

                Companies::make()
                    ->excludeFromSettings()
                    ->strictlyForCreation()
                    ->hideFromIndex()
                    ->order(1001),

                Contacts::make()
                    ->excludeFromSettings()
                    ->strictlyForCreation()
                    ->hideFromIndex()
                    ->order(1002),

                DateTime::make('owner_assigned_date', __('fields.deals.owner_assigned_date'))
                    ->exceptOnForms()
                    ->hidden(),

                Contacts::make()
                    ->label(__('contact.total'))
                    ->count()
                    ->exceptOnForms()
                    ->hidden(),

                Companies::make()
                    ->label(__('company.total'))
                    ->count()
                    ->exceptOnForms()
                    ->hidden(),

                MorphToMany::make('unreadEmailsForUser', __('inbox.unread_count'))
                    ->count()
                    ->exceptOnForms()
                    ->excludeFromZapierResponse()
                    ->hidden(),

                MorphToMany::make('incompleteActivitiesForUser', __('activity.incomplete_activities'))
                    ->count()
                    ->exceptOnForms()
                    ->excludeFromZapierResponse()
                    ->hidden(),

                NextActivityDate::make(),

                ImportNote::make(),

                DateTime::make('updated_at', __('app.updated_at'))
                    ->excludeFromImportSample()
                    ->strictlyForIndex()
                    ->hidden(),

                DateTime::make('created_at', __('app.created_at'))
                    ->excludeFromImportSample()
                    ->strictlyForIndex()
                    ->hidden(),
            ];
    }
}
