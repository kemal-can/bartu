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

namespace App\Innoclapps\Fields;

use ReflectionClass;
use Illuminate\Support\Arr;
use App\Innoclapps\Filesystem;
use App\Innoclapps\Facades\Fields;
use App\Innoclapps\Contracts\Fields\Customfieldable;
use App\Innoclapps\Contracts\Repositories\CustomFieldRepository;

class Manager
{
    /**
     * Hold all groups and fields
     *
     * @var array
     */
    protected array $fields = [];

    /**
     * Loaded fields cache
     *
     * @var array
     */
    protected static array $loaded = [];

    /**
     * Register fields with group
     *
     * @param string $group
     * @param mixed $provider
     *
     * @return static
     */
    public function group($group, $provider)
    {
        static::flushCache();

        if (! isset($this->fields[$group])) {
            $this->fields[$group] = [];
        }

        $this->fields[$group][] = $provider;

        return $this;
    }

    /**
     * Add fields to the given group
     *
     * @param string $group
     * @param mixed $provider
     *
     * @return static
     */
    public function add($group, $provider)
    {
        return $this->group($group, $provider);
    }

    /**
     * Replace the group fields with the given fields
     *
     * @param string $group
     * @param mixed $provider
     *
     * @return static
     */
    public function replace($group, $provider)
    {
        $this->fields[$group] = [];

        return $this->group($group, $provider);
    }

    /**
     * Resolves fields for the given group and view
     *
     * @param string $group
     * @param string $view create|update
     *
     * @return \App\Innoclapps\Fields\Collection
     */
    public function resolve(string $group, string $view)
    {
        return $this->{'resolve' . ucfirst($view) . 'Fields'}($group);
    }

    /**
     * Resolves fields for the given group and view for display
     *
     * @param string $group
     * @param string $view create|update
     *
     * @return \App\Innoclapps\Fields\Collection
     */
    public function resolveForDisplay(string $group, string $view)
    {
        return $this->{'resolve' . ucfirst($view) . 'FieldsForDisplay'}($group);
    }

    /**
     * Resolve the create fields for display
     *
     * @param string $group
     *
     * @return \App\Innoclapps\Fields\Collection
     */
    public function resolveCreateFieldsForDisplay(string $group)
    {
        return $this->resolveCreateFields($group)
            ->reject(fn ($field) => $field->showOnCreation === false)
            ->values();
    }

    /**
     * Resolve the update fields for display
     *
     * @param string $group
     *
     * @return \App\Innoclapps\Fields\Collection
     */
    public function resolveUpdateFieldsForDisplay(string $group)
    {
        return $this->resolveUpdateFields($group)
            ->reject(fn ($field) => $field->showOnUpdate === false)
            ->values();
    }

    /**
     * Resolve the detail fields for display
     *
     * @param string $group
     *
     * @return \App\Innoclapps\Fields\Collection
     */
    public function resolveDetailFieldsForDisplay(string $group)
    {
        return $this->resolveDetailFields($group)
            ->reject(fn ($field) => $field->showOnDetail === false)
            ->values();
    }

    /**
     * Resolve the create fields for the given group
     *
     * @param string $group
     *
     * @return \App\Innoclapps\Fields\Collection
     */
    public function resolveCreateFields(string $group)
    {
        return $this->resolveAndAuthorize($group, Fields::CREATE_VIEW)
            ->filter->isApplicableForCreation()->values();
    }

    /**
     * Resolve the update fields for the given group
     *
     * @param string $group
     *
     * @return \App\Innoclapps\Fields\Collection
     */
    public function resolveUpdateFields(string $group)
    {
        return $this->resolveAndAuthorize($group, Fields::UPDATE_VIEW)
            ->filter->isApplicableForUpdate()->values();
    }

    /**
     * Resolve the detail fields for the given group
     *
     * @param string $group
     *
     * @return \App\Innoclapps\Fields\Collection
     */
    public function resolveDetailFields(string $group)
    {
        return $this->resolveAndAuthorize($group, Fields::DETAIL_VIEW)
            ->filter->isApplicableForDetail()->values();
    }

    /**
     * Resolve and authorize the fields for the given group
     *
     * @param string $group
     * @param string|null $view
     *
     * @return \App\Innoclapps\Fields\Collection
     */
    public function resolveAndAuthorize(string $group, ?string $view = null)
    {
        return $this->inGroup($group, $view)->filter->authorizedToSee();
    }

    /**
     * Resolve the fields intended for settings
     *
     * @param string $group
     * @param string $view
     *
     * @return \App\Innoclapps\Fields\Collection
     */
    public function resolveForSettings(string $group, string $view)
    {
        return $this->resolveAndAuthorize($group, $view)->reject(function ($field) use ($view) {
            return is_bool($field->excludeFromSettings) ? $field->excludeFromSettings : $field->excludeFromSettings === $view;
        })->values();
    }

    /**
     * Get all fields in specific group
     *
     * @param string $group
     *
     * @return \App\Innoclapps\Fields\Collection
     */
    public function inGroup(string $group, ?string $view = null)
    {
        if (isset(static::$loaded[$cacheKey = (string) $group . $view])) {
            return static::$loaded[$cacheKey];
        }

        $callback = function ($field, $index) use ($group, $view) {
            /**
             * Apply any custom attributes added by the user via settings
             */
            $field = $this->applyCustomizedAttributes($field, $group, $view);

            /**
             * Add field order if there is no customized order
             * This helps to sort them properly by default the way they are defined
             */
            $field->order ??= $index + 1 ;

            return $field;
        };

        return static::$loaded[$cacheKey] = $this->load($group)->map($callback)
            ->sortBy('order')
            ->values();
    }

    /**
     * Save the customized fields
     *
     * @param array $data
     * @param string $group Fields group
     * @param string $view Fields view (create|update)
     *
     * @return void
     */
    public function customize($data, $group, $view)
    {
        settings([$this->customizedKey($group, $view) => json_encode($data)]);

        static::flushCache();
    }

    /**
     * Get the customized fields
     *
     * @param string $group Fields group
     * @param string $group Fields view (create|update)
     *
     * @return \Illuminate\Support\Collection
     */
    public function customized($group, $view)
    {
        $customized = settings()->get($this->customizedKey($group, $view), '[]');

        return json_decode($customized);
    }

    /**
     * Purge the customized fields cache
     *
     * @return static
     */
    public static function flushCache()
    {
        static::$loaded = [];
    }

    /**
     * Get the fields that can be created as custom fields
     *
     * @return array
     */
    public function customFieldsAble()
    {
        return once(function () {
            return Filesystem::listClassFilesOfSubclass(Customfieldable::class, __DIR__);
        });
    }

    /**
     * Get the multi option able (custom) fields types
     *
     * @return array
     */
    public function getOptionableFieldsTypes()
    {
        return collect($this->customFieldsAble())
            ->map(fn ($field) => new ReflectionClass($field))
            ->map(fn ($class) => $class->newInstanceWithoutConstructor())
            ->filter->isOptionable()
            ->map(fn ($class) => class_basename($class))->values()->all();
    }

    /**
     * Get non optionable custom fields types
     *
     * @return array
     */
    public function getNonOptionableFieldsTypes()
    {
        return array_diff($this->customFieldsTypes(), $this->getOptionableFieldsTypes());
    }

    /**
     * Get the available custom fields types
     *
     * @return array
     */
    public function customFieldsTypes()
    {
        return collect($this->customFieldsAble())
            ->map(fn ($field) => class_basename($field))
            ->values()
            ->all();
    }

    /**
     * Get custom field class name by given type
     *
     * @param string $type
     *
     * @return string
     */
    public function customFieldByType($type)
    {
        return collect($this->customFieldsAble())
            ->first(fn ($field) => class_basename($field) === $type);
    }

    /**
     * Get the defined custom fields for the given resource
     *
     * @param string $resourceName
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCustomFieldsForResource($resourceName)
    {
        return resolve(CustomFieldRepository::class)->forResource($resourceName)
            ->map(fn ($field) => CustomFieldFactory::createInstance($field));
    }

    /**
     * Loaded the provided group fields
     *
     * @param string $group
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    protected function load($group)
    {
        $fields = new FieldsCollection();

        foreach ($this->fields[$group] ?? [] as $provider) {
            if ($provider instanceof Field) {
                $provider = [$provider];
            }

            if (is_array($provider)) {
                $fields = $fields->merge($provider);
            } elseif (is_callable($provider)) { // callable, closure, __invoke
                $fields = $fields->merge(call_user_func($provider));
            }
        }

        return $fields->merge($this->getCustomFieldsForResource($group));
    }

    /**
     * Create customized key for settings
     *
     * @param string $group
     * @param string $view
     *
     * @return string
     */
    protected function customizedKey($group, $view)
    {
        return "fields-{$group}-{$view}";
    }

    /**
     * Get the allowed customize able attributes
     *
     * @return array
     */
    public function allowedCustomizableAttributes()
    {
        return ['order', 'showOnCreation', 'showOnUpdate', 'showOnDetail', 'collapsed', 'isRequired'];
    }

    /**
     * Get the allowed customize able attributes
     *
     * @return array
     */
    public function allowedCustomizableAttributesForPrimary()
    {
        return ['order'];
    }

    /**
     * Apply any customized options by user
     *
     * @param \App\Innoclapps\Fields\Field $field
     * @param string $group Fields group
     * @param string $view Fields view
     *
     * @return \App\Innoclapps\Fields\Field
     */
    protected function applyCustomizedAttributes($field, $group, $view)
    {
        $allowedForAll = $this->allowedCustomizableAttributes();

        // Protected the primary fields visibility and collapse options when direct API request
        // e.q. the field visibility is set to false when it must be visible
        // because the field is marked as primary field
        $allowedForPrimary = $this->allowedCustomizableAttributesForPrimary();

        if ($view && $customizedData = $this->customized($group, $view)) {
            if (isset($customizedData->{$field->attribute})) {
                foreach (Arr::only(
                    get_object_vars($customizedData->{$field->attribute}),
                    $field->isPrimary() ? $allowedForPrimary : $allowedForAll
                ) as $customAttribute => $value) {
                    if ($customAttribute === 'isRequired' && $value == true) {
                        $field->rules(['sometimes', 'required']);
                    } else {
                        $field->{$customAttribute} = $value;
                    }
                }
            }
        }

        return $field;
    }
}
