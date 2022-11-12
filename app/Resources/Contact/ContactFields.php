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

namespace App\Resources\Contact;

use App\Models\Contact;
use App\Support\Fields\Phone;
use App\Innoclapps\Fields\Text;
use App\Innoclapps\Fields\User;
use App\Innoclapps\Fields\Email;
use App\Innoclapps\Facades\Fields;
use App\Innoclapps\Fields\Country;
use App\Support\Fields\ImportNote;
use App\Innoclapps\Fields\DateTime;
use App\Support\CountryCallingCode;
use App\Http\Resources\UserResource;
use App\Resources\Deal\Fields\Deals;
use App\Innoclapps\Fields\MorphToMany;
use App\Resources\Source\Fields\Source;
use App\Resources\Company\Fields\Companies;
use App\Innoclapps\Fields\IntroductionField;
use App\Resources\Activity\Fields\NextActivityDate;

class ContactFields
{
    /**
    * Provides the contact resource available fields
    *
    * @param \App\Innoclapps\Resources\Resource $resource
    * @param \Illuminate\Http\Request $request
    *
    * @return array
    */
    public function __invoke($resource, $request)
    {
        return [

            Text::make('display_name', __('contact.contact'))
                ->primary()
                ->excludeFromExport()
                ->excludeFromImport()
                ->tapIndexColumn(
                    fn ($column) => $column->width('340px')
                        ->minWidth('340px')
                        ->queryAs(Contact::nameQueryExpression('display_name'))
                )
                ->excludeFromZapierResponse()
                ->strictlyForIndex(),

                Text::make('first_name', __('fields.contacts.first_name'))
                    ->primary()
                    ->creationRules(['required', 'string'])
                    ->updateRules(['filled', 'string'])
                    ->excludeFromDetail()
                    ->excludeFromSettings(Fields::DETAIL_VIEW)
                    ->excludeFromIndex()
                    ->rules('max:191')
                    ->required(true),

                Text::make('last_name', __('fields.contacts.last_name'))
                    ->rules(['nullable', 'string', 'max:191'])
                    ->excludeFromDetail()
                    ->excludeFromSettings(Fields::DETAIL_VIEW)
                    ->excludeFromIndex(),

                Email::make('email', __('fields.contacts.email'))
                    ->rules(['nullable', 'email', 'max:191'])
                    ->unique(Contact::class)
                    ->unique(\App\Models\User::class)
                    ->validationMessages([
                    'unique' => __('contact.validation.email.unique'),
                    ])
                    ->showValueWhenUnauthorizedToView()
                    ->tapIndexColumn(fn ($column) => $column->queryWhenHidden()),

                    Phone::make('phones', __('fields.contacts.phone'))->requireCallingPrefix(
                        function () use ($resource) {
                            if ((bool) settings('require_calling_prefix_on_phones')) {
                                return CountryCallingCode::guess($resource) ?: true;
                            }
                        }
                    )->unique(
                        \App\Models\Contact::class,
                        __('contact.validation.phone.unique')
                    ),

                        User::make(__('fields.contacts.user.name'))
                            ->primary()
                            ->withMeta(['attributes' => ['placeholder' => __('app.no_owner')]])
                            ->notification(\App\Notifications\UserAssignedToContact::class)
                            ->setJsonResource(UserResource::class)
                            ->trackChangeDate('owner_assigned_date')
                            ->tapIndexColumn(fn ($column) => $column->primary(false))
                            ->excludeFromDetail()
                            ->excludeFromSettings(Fields::DETAIL_VIEW)
                            ->showValueWhenUnauthorizedToView(),

                        Source::make(),

                        IntroductionField::make(__('resource.associate_with_records'))
                            ->strictlyForCreation()
                            ->titleIcon('Link')
                            ->order(1000),

                        Companies::make()
                            ->excludeFromSettings()
                            ->strictlyForCreation()
                            ->hideFromIndex()
                            ->order(1001),

                        Deals::make()
                            ->excludeFromSettings()
                            ->strictlyForCreation()
                            ->hideFromIndex()
                            ->order(1002),

                        DateTime::make('owner_assigned_date', __('fields.contacts.owner_assigned_date'))
                            ->strictlyForIndex()
                            ->excludeFromImport()
                            ->hidden(),

                        Companies::make()
                            ->label(__('company.total'))
                            ->count()
                            ->exceptOnForms()
                            ->hidden(),

                        Deals::make()
                            ->label(__('deal.total'))
                            ->count()
                            ->exceptOnForms()
                            ->hidden(),

                        Deals::make('authorizedOpenDeals')
                            ->label(__('deal.open_deals'))
                            ->count()
                            ->exceptOnForms()
                            ->excludeFromZapierResponse()
                            ->hidden(),

                        Deals::make('authorizedClosedDeals')
                            ->label(__('deal.closed_deals'))
                            ->count()
                            ->exceptOnForms()
                            ->excludeFromZapierResponse()
                            ->hidden(),

                        Deals::make('authorizedWonDeals')
                            ->label(__('deal.won_deals'))
                            ->count()
                            ->exceptOnForms()
                            ->excludeFromZapierResponse()
                            ->hidden(),

                        Deals::make('authorizedLostDeals')
                            ->label(__('deal.lost_deals'))
                            ->count()
                            ->exceptOnForms()
                            ->excludeFromZapierResponse()
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

                        Text::make('job_title', __('fields.contacts.job_title'))
                            ->rules(['nullable', 'string', 'max:191'])
                            ->collapsed()
                            ->hideFromIndex()
                            ->hideWhenCreating(),

                        Text::make('street', __('fields.contacts.street'))
                            ->rules(['nullable', 'string', 'max:191'])
                            ->collapsed()
                            ->hideFromIndex()
                            ->hideWhenCreating(),

                        Text::make('city', __('fields.contacts.city'))
                            ->rules(['nullable', 'string', 'max:191'])
                            ->collapsed()
                            ->hideFromIndex()
                            ->hideWhenCreating(),

                        Text::make('state', __('fields.contacts.state'))
                            ->rules(['nullable', 'string', 'max:191'])
                            ->collapsed()
                            ->hideFromIndex()
                            ->hideWhenCreating(),

                        Text::make('postal_code', __('fields.contacts.postal_code'))
                            ->rules(['nullable', 'max:191'])
                            ->collapsed()
                            ->hideFromIndex()
                            ->hideWhenCreating(),

                        Country::make(__('fields.contacts.country.name'))
                            ->collapsed()
                            ->hideFromIndex()
                            ->hideWhenCreating(),

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
