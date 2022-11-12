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

use App\Support\Fields\Phone;
use App\Innoclapps\Fields\Text;
use App\Innoclapps\Fields\User;
use App\Innoclapps\Fields\Email;
use App\Innoclapps\Fields\Domain;
use App\Innoclapps\Facades\Fields;
use App\Innoclapps\Fields\Country;
use App\Support\Fields\ImportNote;
use App\Innoclapps\Fields\DateTime;
use App\Support\CountryCallingCode;
use App\Http\Resources\UserResource;
use App\Innoclapps\Fields\BelongsTo;
use App\Resources\Deal\Fields\Deals;
use App\Innoclapps\Facades\Innoclapps;
use App\Innoclapps\Fields\MorphToMany;
use App\Models\Company as CompanyModel;
use App\Resources\Source\Fields\Source;
use App\Http\Resources\IndustryResource;
use App\Resources\Company\Fields\Company;
use App\Resources\Contact\Fields\Contacts;
use App\Innoclapps\Fields\IntroductionField;
use App\Contracts\Repositories\IndustryRepository;
use App\Resources\Activity\Fields\NextActivityDate;

class CompanyFields
{
    /**
    * Provides the company resource available fields
    *
    * @param \App\Innoclapps\Resources\Resource $resource
    * @param \Illuminate\Http\Request $request
    *
    * @return array
    */
    public function __invoke($resource, $request)
    {
        return [
            Text::make('name', __('fields.companies.name'))
                ->tapIndexColumn(fn ($column) => $column->width('340px')->minWidth('340px'))
                ->creationRules(['required', 'string'])
                ->updateRules(['filled', 'string'])
                ->rules('max:191')
                ->required(true)
                ->excludeFromDetail()
                ->excludeFromSettings(Fields::DETAIL_VIEW)
                ->primary(),

            Domain::make('domain', __('fields.companies.domain'))
                ->rules(['nullable', 'string', 'max:191'])
                ->hideFromIndex(),

            Email::make('email', __('fields.companies.email'))
                ->rules('max:191')
                ->unique(CompanyModel::class)
                ->validationMessages([
                    'unique' => __('company.validation.email.unique'),
                ]),

            BelongsTo::make('industry', IndustryRepository::class, __('fields.companies.industry.name'))
                ->setJsonResource(IndustryResource::class)
                ->options(Innoclapps::resourceByName('industries'))
                ->acceptLabelAsValue()
                ->hidden(),

            Phone::make('phones', __('fields.companies.phone'))->requireCallingPrefix(
                function () use ($resource) {
                    if ((bool) settings('require_calling_prefix_on_phones')) {
                        return CountryCallingCode::guess($resource) ?: true;
                    }
                }
            )->hidden(),

                Contacts::make()
                    ->label(__('contact.total'))
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

                Source::make()
                    ->collapsed()
                    ->hideWhenCreating(),

                Company::make('parent', __('fields.companies.parent.name'), 'parent_company_id')
                    ->collapsed()
                    ->hideFromIndex()
                    ->hideWhenCreating()
                    ->excludeFromImport(),

                Text::make('street', __('fields.companies.street'))
                    ->collapsed()
                    ->hideFromIndex()
                    ->hideWhenCreating()
                    ->rules(['nullable', 'string', 'max:191']),

                Text::make('city', __('fields.companies.city'))
                    ->collapsed()
                    ->hideFromIndex()
                    ->hideWhenCreating()
                    ->rules(['nullable', 'string', 'max:191']),

                Text::make('state', __('fields.companies.state'))
                    ->collapsed()
                    ->hideFromIndex()
                    ->hideWhenCreating()
                    ->rules(['nullable', 'string', 'max:191']),

                Text::make('postal_code', __('fields.companies.postal_code'))
                    ->collapsed()
                    ->hideFromIndex()
                    ->hideWhenCreating()
                    ->rules(['nullable', 'max:191']),

                Country::make(__('fields.companies.country.name'))
                    ->collapsed()
                    ->hideFromIndex()
                    ->hideWhenCreating(),

                User::make(__('fields.companies.user.name'))
                    ->primary() // Primary field to show the owner in the form
                    ->withMeta(['attributes' => ['placeholder' => __('app.no_owner')]])
                    ->setJsonResource(UserResource::class)
                    ->tapIndexColumn(fn ($column) => $column->primary(false))
                    ->notification(\App\Notifications\UserAssignedToCompany::class)
                    ->trackChangeDate('owner_assigned_date')
                    ->excludeFromDetail()
                    ->excludeFromSettings(Fields::DETAIL_VIEW)
                    ->showValueWhenUnauthorizedToView(),

                IntroductionField::make(__('resource.associate_with_records'))
                    ->strictlyForCreation()
                    ->titleIcon('Link')
                    ->order(1000),

                Deals::make()
                    ->excludeFromSettings()
                    ->strictlyForCreation()
                    ->hideFromIndex()
                    ->order(1001),

                Contacts::make()
                    ->excludeFromSettings()
                    ->strictlyForCreation()
                    ->hideFromIndex()
                    ->order(1002),

                DateTime::make('owner_assigned_date', __('fields.companies.owner_assigned_date'))
                    ->exceptOnForms()
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
