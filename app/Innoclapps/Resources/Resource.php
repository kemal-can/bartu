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

namespace App\Innoclapps\Resources;

use JsonSerializable;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Innoclapps\Facades\Menu;
use App\Innoclapps\Facades\Cards;
use App\Innoclapps\Facades\Fields;
use App\Innoclapps\ResolvesActions;
use App\Innoclapps\ResolvesFilters;
use App\Innoclapps\Facades\Innoclapps;
use App\Innoclapps\Settings\SettingsMenu;
use App\Innoclapps\Fields\CustomFieldFactory;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Innoclapps\Contracts\Resources\Resourceful;
use App\Innoclapps\Contracts\Resources\ResourcefulRequestHandler;

abstract class Resource implements JsonSerializable
{
    use ResolvesActions,
        ResolvesFilters,
        QueriesResources,
        ResolvesTables;

    /**
     * The column the records should be default ordered by when retrieving
     *
     * @var string
     */
    public static string $orderBy = 'id';

    /**
     * The direction the records should be default ordered by when retrieving
     *
     * @var string
     */
    public static string $orderByDir = 'asc';

    /**
     * Indicates whether the resource is globally searchable
     *
     * @var boolean
     */
    public static bool $globallySearchable = false;

    /**
     * Indicates whether the resource fields are customizeable
     *
     * @var boolean
     */
    public static bool $fieldsCustomizable = false;

    /**
     * Indicates whether the resource has Zapier hooks
     *
     * @var boolean
     */
    public static bool $hasZapierHooks = false;

    /**
     * Resource model
     *
     * @var \Illuminate\Database\Eloquent\Model|null
     */
    public $model;

    /**
     * Initialize new Resource class
     */
    public function __construct()
    {
        $this->register();
    }

    /**
     * Get the underlying resource repository
     *
     * @return \App\Innoclapps\Repository\AppRepository
     */
    abstract public static function repository();

    /**
     * Get the resource underlying model class name
     *
     * @return string
     */
    public static function model()
    {
        return static::repository()->model();
    }

    /**
     * Set the resource model
     *
     * @param \Illuminate\Database\Eloquent\Model|null $model
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * Set the resource available cards
     *
     * @return array
     */
    public function cards() : array
    {
        return [];
    }

    /**
     *  Get the filters intended for the resource
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function filtersForResource(ResourceRequest $request)
    {
        return $this->resolveFilters($request)->merge(
            (new CustomFieldFactory($this->name()))->createFieldsForFilters()
        );
    }

    /**
     * Get the actions intended for the resource
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return \Illuminate\Support\Collection
     */
    public function actionsForResource(ResourceRequest $request)
    {
        return $this->resolveActions($request);
    }

    /**
      * Get the json resource that should be used for json response
      *
      * @return string|null
      */
    public function jsonResource() : ?string
    {
        return null;
    }

    /**
     * Create JSON resource
     *
     * @param mixed $data
     * @param boolean $resolve Indicates whether to resolve the resource
     * @param \App\Innoclapps\Resources\Http\ResourceRequest|null $request
     *
     * @return mixed
     */
    public function createJsonResource($data, $resolve = false, ?ResourceRequest $request = null)
    {
        $collection = is_countable($data);

        if ($collection) {
            $resource = $this->jsonResource()::collection($data);
        } else {
            $jsonResource = $this->jsonResource();
            $resource     = new $jsonResource($data);
        }

        if ($resolve) {
            $request = $request ?: app(ResourceRequest::class)->setResource($this->name());

            if (! $collection) {
                $request->setResourceId($data->getKey());
            }

            return $resource->resolve($request);
        }

        return $resource;
    }

    /**
     * Get the fields that should be included in JSON resource
     *
     * @param \App\Innoclapps\Resources\Http\Request $request
     * @param \App\Innoclapps\Models\Model $model
     *
     * Indicates whether the current user can see the model in the JSON resource
     * @param boolean $canSeeResource
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    public function getFieldsForJsonResource($request, $model, $canSeeResource = true)
    {
        return $this->resolveFields()->reject(function ($field) use ($request) {
            return $field->excludeFromZapierResponse && $request->isZapier();
        })->filter(function ($field) use ($canSeeResource) {
            if (! $canSeeResource) {
                return $field->alwaysInJsonResource === true;
            }

            return $canSeeResource;
        })->reject(function ($field) use ($model) {
            return is_null($field->resolveForJsonResource($model));
        })->values();
    }

    /**
     * Set the available resource fields
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function fields(Request $request) : array
    {
        return [];
    }

    /**
     * Get the resource defined fields
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    public static function getFields()
    {
        return Fields::inGroup(static::name());
    }

    /**
     * Resolve the create fields for resource
     *
     * @return \App\Innoclapps\Fields\Collection
     */
    public function resolveCreateFields()
    {
        return Fields::resolveCreateFields(static::name());
    }

    /**
     * Resolve the update fields for the resource
     *
     * @return \App\Innoclapps\Fields\Collection
     */
    public function resolveUpdateFields()
    {
        return Fields::resolveUpdateFields(static::name());
    }

    /**
     * Resolve the resource fields for display
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    public function resolveFields()
    {
        return static::getFields()->filter->authorizedToSee()->values();
    }

    /**
     * Set the resource rules available for create and update
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function rules(Request $request)
    {
        return [];
    }

    /**
     * Set the resource rules available only for create
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function createRules(Request $request)
    {
        return [];
    }

    /**
     * Set the resource rules available only for update
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function updateRules(Request $request)
    {
        return [];
    }

    /**
     * Set the criteria that should be used to fetch only own data for the user
     *
     * @return string|null
     */
    public function ownCriteria() : ?string
    {
        return null;
    }

    /**
     * Get the resource relationship name when it's associated
     *
     * @return string|null
     */
    public function associateableName() : ?string
    {
        return null;
    }

    /**
     * Set the menu items for the resource
     *
     * @return array
     */
    public function menu() : array
    {
        return [];
    }

    /**
     * Get the settings menu items for the resource
     *
     * @return array
     */
    public function settingsMenu() : array
    {
        return [];
    }

    /**
     * Register permissions for the resource
     *
     * @return void
     */
    public function registerPermissions() : void
    {
    }

    /**
     * Get the custom validation messages for the resource
     * Useful for resources without fields.
     *
     * @return array
     */
    public function validationMessages() : array
    {
        return [];
    }

    /**
     * Determine whether the resource has associations
     *
     * @return boolean
     */
    public function isAssociateable() : bool
    {
        return ! is_null($this->associateableName());
    }

    /**
     * Get the resource available associative resources
     *
     * @return \Illuminate\Support\Collection
     */
    public function availableAssociations()
    {
        return Innoclapps::registeredResources()
            ->reject(fn ($resource) => is_null($resource->associateableName()))
            ->filter(fn ($resource) => app(static::model())->isRelation($resource->associateableName()))
            ->values();
    }

    /**
     * Check whether the given resource can be associated to the current resource
     *
     * @param string $resourceName
     *
     * @return boolean
     */
    public function canBeAssociated(string $resourceName) : bool
    {
        return (bool) $this->availableAssociations()->first(
            fn ($resource) => $resource->name() == $resourceName
        );
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

        return count($this->fields($request)) > 0 ?
            new ResourcefulHandlerWithFields($request, $repository) :
            new ResourcefulHandler($request, $repository);
    }

    /**
     * Determine if this resource is searchable.
     *
     * @return boolean
     */
    public static function searchable() : bool
    {
        return ! empty(static::repository()->getFieldsSearchable());
    }

    /**
     * Get the displayable label of the resource.
     *
     * @return string
     */
    public static function label() : string
    {
        return Str::plural(Str::title(Str::snake(class_basename(get_called_class()), ' ')));
    }

    /**
     * Get the displayable singular label of the resource.
     *
     * @return string
     */
    public static function singularLabel() : string
    {
        return Str::singular(static::label());
    }

    /**
     * Get the internal name of the resource.
     *
     * @return string
     */
    public static function name() : string
    {
        return Str::plural(Str::kebab(class_basename(get_called_class())));
    }

    /**
     * Get the internal singular name of the resource.
     *
     * @return string
     */
    public static function singularName() : string
    {
        return Str::singular(static::name());
    }

    /**
     * Get the resource importable class
     *
     * @return \App\Innoclapps\Resources\Import
     */
    public function importable() : Import
    {
        return new Import($this);
    }

    /**
     * Get the resource import sample class
     *
     * @return \App\Innoclapps\Resources\ImportSample
     */
    public function importSample() : ImportSample
    {
        return new ImportSample($this);
    }

    /**
     * Get the resource export class
     *
     * @param \App\Innoclapps\Repository\BaseRepository $repository
     *
     * @return \App\Innoclapps\Resources\Export
     */
    public function exportable($repository) : Export
    {
        return new Export($this, $repository);
    }

    /**
     * Register the resource available menu items
     *
     * @return void
     */
    protected function registerMenuItems() : void
    {
        Innoclapps::booting(function () {
            foreach ($this->menu() as $item) {
                if (! $item->singularName) {
                    $item->singularName($this->singularLabel());
                }

                Menu::register($item);
            }
        });
    }

    /**
     * Register the resource available cards
     *
     * @return void
     */
    protected function registerCards() : void
    {
        Cards::register([
            'name' => $this->name(),
            'as'   => $this->label(),
        ], $this->cards());
    }

    /**
     * Register the resource available CRUD fields
     *
     * @return void
     */
    protected function registerFields() : void
    {
        if ($this instanceof Resourceful) {
            Fields::group($this->name(), function () {
                return $this->fields(request());
            });
        }
    }

    /**
     * Register common permissions for the resource
     *
     * @return void
     */
    protected function registerCommonPermissions() : void
    {
        if ($callable = config('innoclapps.resources.permissions.common')) {
            (new $callable)($this);
        }
    }

    /**
     * Register the resource information
     *
     * @return void
     */
    protected function register() : void
    {
        $this->registerPermissions();
        $this->registerMenuItems();
        $this->registerFields();
        $this->registerCards();

        Innoclapps::booting(function () {
            foreach ($this->settingsMenu() as $key => $item) {
                SettingsMenu::register($item, is_int($key) ? $this->name() : $key);
            }
        });
    }

    /**
     * Serialize the resource
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return [
            'name'               => $this->name(),
            'label'              => $this->label(),
            'singularLabel'      => $this->singularLabel(),
            'fieldsCustomizable' => static::$fieldsCustomizable,
        ];
    }
}
