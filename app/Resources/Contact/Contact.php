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

use Illuminate\Http\Request;
use App\Innoclapps\Table\Table;
use App\Innoclapps\Filters\Operand;
use App\Innoclapps\Resources\Import;
use App\Innoclapps\Resources\Resource;
use App\Http\Resources\ContactResource;
use App\Resources\Actions\DeleteAction;
use App\Innoclapps\Menu\Item as MenuItem;
use App\Criteria\Contact\OwnContactsCriteria;
use App\Http\View\FrontendComposers\Template;
use App\Support\Filters\AddressOperandFilter;
use App\Innoclapps\Filters\Text as TextFilter;
use App\Support\Filters\ResourceUserTeamFilter;
use App\Innoclapps\Contracts\Resources\HasEmail;
use App\Innoclapps\Contracts\Resources\Mediable;
use App\Contracts\Repositories\ContactRepository;
use App\Innoclapps\Contracts\Resources\Tableable;
use App\Resources\Contact\Frontend\ViewComponent;
use App\Innoclapps\Contracts\Resources\Exportable;
use App\Innoclapps\Contracts\Resources\Importable;
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
use App\Innoclapps\Contracts\Resources\ResourcefulRequestHandler;
use App\Innoclapps\Criteria\SearchByFirstNameAndLastNameCriteria;
use App\Resources\Activity\Filters\ResourceNextActivityDate as ResourceNextActivityDateFilter;

class Contact extends Resource implements Resourceful, Tableable, Mediable, Importable, Exportable, HasEmail, AcceptsCustomFields
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
    public static string $orderBy = 'first_name';

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
        return tap(resolve(ContactRepository::class), function ($repository) {
            // When search_fields exists in request for the RequestCriteria
            // we will prevent using the SearchByFirstNameAndLastNameCriteria criteria
            // to avoid unnecessary and not-accurate searches
            if (request()->isSearching() && request()->missing('search_fields')) {
                $repository->appendToRequestCriteria(new SearchByFirstNameAndLastNameCriteria);
            }
        });
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
            MenuItem::make(static::label(), '/contacts', 'Users')
                ->position(20)
                ->inQuickCreate()
                ->keyboardShortcutChar('C'),
        ];
    }

    /**
    * Get the resource relationship name when it's associated
    *
    * @return string
    */
    public function associateableName() : string
    {
        return 'contacts';
    }

    /**
    * Get the resource available cards
    *
    * @return array
    */
    public function cards() : array
    {
        return [
            (new Cards\ContactsByDay)->refreshOnActionExecuted()->help(__('app.cards.creation_date_info')),
            (new Cards\ContactsBySource)->refreshOnActionExecuted()->help(__('app.cards.creation_date_info'))->color('info'),
            (new Cards\RecentlyCreatedContacts)->onlyOnDashboard(),
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
        $repository->appendToRequestCriteria(new SearchByFirstNameAndLastNameCriteria);

        return new ContactTable($repository, $request);
    }

    /**
    * Get the json resource that should be used for json response
    *
    * @return string
    */
    public function jsonResource() : string
    {
        return ContactResource::class;
    }

    /**
    * Get the criteria that should be used to fetch only own data for the user
    *
    * @return string
    */
    public function ownCriteria() : string
    {
        return OwnContactsCriteria::class;
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
        return (new ContactFields)($this, $request);
    }

    /**
    * Get the resourceful CRUD handler class
    *
    * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
    * @param \App\Innoclapps\Repository\AppRepository|null $repository
    *
    * @return \App\Innoclapps\Contracts\Resources\ResourcefulRequestHandler
    */
    public function resourcefulHandler(ResourceRequest $request, $repository = null) : ResourcefulRequestHandler
    {
        $repository ??= static::repository();

        return new ResourcefulHandler($request, $repository);
    }

    /**
    * Get the resource importable class
    *
    * @return \App\Innoclapps\Resources\Import
    */
    public function importable() : Import
    {
        return parent::importable()->validateDuplicatesUsing(function ($request) {
            return $this->lookUpForDuplicateImportContact($request);
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

            TextFilter::make('first_name', __('fields.contacts.first_name'))->withoutEmptyOperators(),

            TextFilter::make('last_name', __('fields.contacts.last_name')),

            TextFilter::make('email', __('fields.contacts.email')),

            UserFilter::make(__('fields.contacts.user.name')),

            ResourceUserTeamFilter::make(__('team.owner_team')),

            DateTimeFilter::make('owner_assigned_date', __('fields.contacts.owner_assigned_date')),

            ResourceActivitiesFilter::make(),

            SourceFilter::make(),

            TextFilter::make('job_title', __('fields.contacts.job_title')),

            AddressOperandFilter::make('contacts'),

            UserFilter::make(__('app.created_by'), 'created_by')->canSeeWhen('view all contacts'),

            HasManyFilter::make('phones', __('fields.contacts.phone'))->setOperands([
                (new Operand('number', __('fields.contacts.phone')))->filter(TextFilter::class),
                ])->hideOperands(),

                ResourceDealsFilter::make(__('contact.contact')),

                ResourceEmailsFilter::make(),

                ResourceNextActivityDateFilter::make(),

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
                        fn ($request, $model) => $request->user()->can('bulk delete contacts')
                    ),
            ];
    }

    /**
    * Try to find a duplicate contact for the import request
    *
    * @param \App\Innoclapps\Resources\Http\ImportRequest $request
    *
    * @return \App\Models\Contact|null
    */
    protected function lookUpForDuplicateImportContact($request)
    {
        // From Laravel docs:
        // Since the cursor method only ever holds a single Eloquent model in memory at a time, it cannot eager load relationships.
        // If you need to eager load relationships, consider using the lazy method instead.
        $collection = once(function () {
            return $this->repository()->with('phones')->lazy(500);
        });

        // First check by email
        if ($request->has('email') && $request->filled('email')) {
            if ($contact = $collection->firstWhere('email', $request->email)) {
                return $contact;
            }
        }

        // Then check by phone number
        if ($request->has('phones') && ! empty($request->phones)) {
            return $collection->first(function ($contact) use ($request) {
                return $contact->phones->contains(function ($phone) use ($request) {
                    return in_array($phone->number, (array) data_get($request->phones, '*.number'));
                });
            });
        }
    }

    /**
    * Get the displayable label of the resource.
    *
    * @return string
    */
    public static function label() : string
    {
        return __('contact.contacts');
    }

    /**
    * Get the displayable singular label of the resource.
    *
    * @return string
    */
    public static function singularLabel() : string
    {
        return __('contact.contact');
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
