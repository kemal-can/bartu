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

use Closure;
use JsonSerializable;
use Illuminate\Support\Arr;
use App\Innoclapps\Makeable;
use App\Innoclapps\Facades\Innoclapps;
use App\Innoclapps\Models\CustomField;
use App\Http\Resources\CustomFieldResource;
use App\Innoclapps\Rules\UniqueResourceRule;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Innoclapps\MailableTemplates\Placeholders\GenericPlaceholder;

class Field extends FieldElement implements JsonSerializable
{
    use HasInputGroup,
        ResolvesValue,
        HasModelEvents,
        DisplaysOnIndex,
        Makeable;

    /**
     * Default value
     *
     * @var mixed
     */
    public $value;

    /**
     * Field attribute / column name
     *
     * @var string
     */
    public $attribute;

    /**
     * Custom field request attribute
     *
     * @var string|null
     */
    public $requestAttribute;

    /**
     * Field label
     *
     * @var string
     */
    public $label;

    /**
     * Help text
     *
     * @var string|null
     */
    public ?string $helpText = null;

    /**
     * Indicates how the help text is displayed, as icon or text
     *
     * @var string
     */
    public string $helpTextDisplay = 'icon';

    /**
     * Whether the field is collapsed. E.q. view all fields
     *
     * @var boolean
     */
    public bool $collapsed = false;

    /**
     * Validation rules
     *
     * @var array
     */
    public array $rules = [];

    /**
     * Validation creation rules
     *
     * @var array
     */
    public array $creationRules = [];

    /**
     * Validation import rules
     *
     * @var array
     */
    public array $importRules = [];

    /**
     * Validation update rules
     *
     * @var array
     */
    public array $updateRules = [];

    /**
     * Custom validation error messages
     *
     * @var array
     */
    public array $validationMessages = [];

    /**
     * Whether the field is primary
     * @var boolean
     */
    public bool $primary = false;

    /**
     * Indicates whether the field is custom field
     *
     * @var null\App\Innoclapps\Models\CustomField
     */
    public ?CustomField $customField = null;

    /**
     * Emit change event when field value changed
     *
     * @var string|null
     */
    public ?string $emitChangeEvent = null;

    /**
     * Is field read only
     *
     * @var boolean|callable
     */
    public $readOnly = false;

    /**
     * Is the field hidden
     *
     * @var boolean
     */
    public bool $displayNone = false;

    /**
     * Indicates whether the column value should be always included in the
     * JSON Resource for front-end
     *
     * @var boolean
     */
    public bool $alwaysInJsonResource = false;

    /**
     * Prepare for validation callback
     *
     * @var callable|null
     */
    public $validationCallback;

    /**
     * Indicates whether the field is excluded from Zapier response
     *
     * @var boolean
     */
    public bool $excludeFromZapierResponse = false;

    /**
     * Field order
     *
     * @var int|null
     */
    public ?int $order;

    /**
     * Field column class
     *
     * @var string|\Closure|null
     */
    public string|Closure|null $colClass = null;

    /**
     * Field toggle indicator
     *
     * @var boolean
     */
    public bool $toggleable = false;

    /**
     * Custom attributes provider for create/update
     *
     * @var callable|null
     */
    public $saveUsing;

    /**
     * Custom callback used to determine if the field is required.
     *
     * @var \Closure|bool
     */
    public $isRequiredCallback;

    /**
     * Field component
     *
     * @var null|string
     */
    public $component = null;

    /**
     * Initialize new Field instance class
     *
     * @param string $attribute field attribute
     * @param string|null $label field label
     */
    public function __construct($attribute, $label = null)
    {
        $this->attribute = $attribute;

        $this->label = $label;

        $this->boot();
    }

    /**
     * Custom boot function
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Set field attribute
     *
     * @param string $attribute
     * @return static
     */
    public function attribute($attribute) : static
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Set field label
     *
     * @param string $label
     * @return static
     */
    public function label($label) : static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Set the field order
     *
     * @param null|integer $order
     *
     * @return static
     */
    public function order(?int $order) : static
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Set the field column class
     *
     * @param string $class
     *
     * @return static
     */
    public function colClass(string|Closure|null $class) : static
    {
        $this->colClass = $class;

        return $this;
    }

    /**
     * Mark the field as toggleable
     *
     * @param boolean $value
     *
     * @return static
     */
    public function toggleable(bool $value = true) : static
    {
        $this->toggleable = $value;

        return $this;
    }

    /**
     * Get the field column class
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return string|null
     */
    public function getColClass(ResourceRequest $request) : ?string
    {
        return with($this->colClass, function ($value) use ($request) {
            if ($value instanceof Closure) {
                return $value($request);
            }

            return $value;
        });
    }

    /**
     * Set default value on creation forms
     *
     * @param mixed $value
     *
     * @return static
     */
    public function withDefaultValue($value) : static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the field default value
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return mixed
     */
    public function defaultValue(ResourceRequest $request) : mixed
    {
        return with($this->value, function ($value) use ($request) {
            if ($value instanceof Closure) {
                return $value($request);
            }

            return $value;
        });
    }

    /**
     * Set collapsible field
     *
     * @param boolean $bool
     *
     * @return static
     */
    public function collapsed(bool $bool = true) : static
    {
        $this->collapsed = $bool;

        return $this;
    }

    /**
     * Set field help text
     *
     * @param null|string $text
     *
     * @return static
     */
    public function help(?string $text) : static
    {
        $this->helpText = $text;

        return $this;
    }

    /**
     * Set the field display of the help text
     *
     * @param string $display icon|text
     *
     * @return static
     */
    public function helpDisplay(string $display) : static
    {
        $this->helpTextDisplay = $display;

        return $this;
    }

    /**
     * Add read only statement
     *
     * @param boolean|callable $value
     *
     * @return static
     */
    public function readOnly(bool|callable $value) : static
    {
        $this->readOnly = $value;

        return $this;
    }

    /**
     * Determine whether the field is read only
     *
     * @return boolean
     */
    public function isReadOnly() : bool
    {
        return with($this->readOnly, function ($callback) {
            return $callback === true || (is_callable($callback) && call_user_func($callback));
        });
    }

    /**
     * Hides the field from the document
     *
     * @param boolean $value
     *
     * @return static
     */
    public function displayNone(bool $value = true) : static
    {
        $this->displayNone = $value;

        return $this;
    }

    /**
     * Get the component name for the field.
     *
     * @return string|null
     */
    public function component() : ?string
    {
        return $this->component;
    }

    /**
     * Set the field as primary
     *
     * @param boolean $bool
     *
     * @return static
     */
    public function primary(bool $bool = true) : static
    {
        $this->primary = $bool;

        return $this;
    }

    /**
     * Check whether the field is primary
     *
     * @return boolean
     */
    public function isPrimary() : bool
    {
        return $this->primary === true;
    }

    /**
     * Set the callback used to determine if the field is required.
     *
     * Useful when you have complex required validation requirements like filled, sometimes etc..,
     * you can manually mark the field as required by passing a boolean when defining the field.
     *
     * This method will only add a "required" indicator to the UI field.
     * You must still define the related requirement rules() that should apply during validation.
     *
     * @param \Closure|bool $callback
     * @return static
     */
    public function required($callback = true) : static
    {
        $this->isRequiredCallback = $callback;

        return $this;
    }

    /**
     * Check whether the field is required based on the rules
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return boolean
     */
    public function isRequired(ResourceRequest $request) : bool
    {
        return with($this->isRequiredCallback, function ($callback) use ($request) {
            if ($callback === true || (is_callable($callback) && call_user_func($callback, $request))) {
                return true;
            }

            if (! empty($this->attribute) && is_null($callback)) {
                if ($request->isCreateRequest()) {
                    $rules = $this->getCreationRules()[$this->requestAttribute()];
                } elseif ($request->isUpdateRequest()) {
                    $rules = $this->getUpdateRules()[$this->requestAttribute()];
                } elseif (Innoclapps::isImportMapping() || Innoclapps::isImportInProgress()) {
                    $rules = $this->getImportRules()[$this->requestAttribute()];
                } else {
                    $rules = $this->getRules()[$this->requestAttribute()];
                }

                return in_array('required', $rules);
            }

            return false;
        });
    }

    /**
     * Check whether the field is unique
     *
     * @return boolean
     */
    public function isUnique() : bool
    {
        foreach ($this->getRules() as $rules) {
            if (collect($rules)->whereInstanceOf(UniqueResourceRule::class)->isNotEmpty()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Mark the field as unique
     *
     * @return static
     */
    public function unique($model, $skipOnImport = true) : static
    {
        $this->rules(UniqueResourceRule::make($model)->skipOnImport($skipOnImport));

        return $this;
    }

    /**
     * Mark the field as not unique
     *
     * @return static
     */
    public function notUnique() : static
    {
        foreach ($this->getRules() as $rules) {
            foreach ($rules as $ruleKey => $rule) {
                if ($rule instanceof UniqueResourceRule) {
                    unset($this->rules[$ruleKey]);
                }
            }
        }

        return $this;
    }

    /**
     * Get the mailable template placeholder
     *
     * @param \App\Innoclapps\Models\Model|null $model
     *
     * @return \App\Innoclapps\MailableTemplates\Placeholders\MailPlaceholder|string|null
     */
    public function mailableTemplatePlaceholder($model)
    {
        return GenericPlaceholder::make()
            ->tag($this->attribute)
            ->description($this->label)
            ->value(function () use ($model) {
                return $this->resolveForDisplay($model);
            });
    }

    /**
     * Provide a callable to prepare the field for validation
     *
     * @param callable $callable
     *
     * @return static
     */
    public function prepareForValidation($callable) : static
    {
        $this->validationCallback = $callable;

        return $this;
    }

    /**
     * Indicates that the field value should be included in the JSON resource
     * when the user is not authorized to view the model/record
     *
     * @return static
     */
    public function showValueWhenUnauthorizedToView() : static
    {
        $this->alwaysInJsonResource = true;

        return $this;
    }

    /**
     * Indicates whether to emit change event when value is changed
     *
     * @param string $eventName
     *
     * @return static
     */
    public function emitChangeEvent($eventName = null) : static
    {
        $this->emitChangeEvent = $eventName ?? 'field-' . $this->attribute . '-value-changed';

        return $this;
    }

    /**
     * Set field validation rules for all requests
     *
     * @param string|array $rules
     *
     * @return static
     */
    public function rules($rules) : static
    {
        $this->rules = array_merge(
            $this->rules,
            is_array($rules) ? $rules : func_get_args()
        );

        return $this;
    }

    /**
     * Set field validation rules that are only applied on create request
     *
     * @param string|array $rules
     *
     * @return static
     */
    public function creationRules($rules) : static
    {
        $this->creationRules = array_merge(
            $this->creationRules,
            is_array($rules) ? $rules : func_get_args()
        );

        return $this;
    }

    /**
     * Set field validation rules for import
     *
     * @param string|array $rules
     *
     * @return static
     */
    public function importRules($rules) : static
    {
        $this->importRules = array_merge(
            $this->importRules,
            is_array($rules) ? $rules : func_get_args()
        );

        return $this;
    }

    /**
     * Get field validation rules for import
     *
     * @return array
     */
    public function getImportRules() : array
    {
        $rules = [
            $this->requestAttribute() => $this->importRules,
        ];

        // We will remove the array rule in case found
        // because the import can handle arrays via coma separated
        return collect(array_merge_recursive(
            $this->getCreationRules(),
            $rules
        ))->reject(fn ($rule) => $rule === 'array')->all();
    }

    /**
     * Set field validation rules that are only applied on update request
     *
     * @param string|array $rules
     *
     * @return static
     */
    public function updateRules($rules) : static
    {
        $this->updateRules = array_merge(
            $this->updateRules,
            is_array($rules) ? $rules : func_get_args()
        );

        return $this;
    }

    /**
     * Get field validation rules for the request
     *
     * @return array
     */
    public function getRules() : array
    {
        return $this->createRulesArray($this->rules);
    }

    /**
     * Get the field validation rules for create request
     *
     * @return array
     */
    public function getCreationRules() : array
    {
        $rules = $this->createRulesArray($this->creationRules);

        return array_merge_recursive(
            $this->getRules(),
            $rules
        );
    }

    /**
     * Get the field validation rules for update request
     *
     * @return array
     */
    public function getUpdateRules() : array
    {
        $rules = $this->createRulesArray($this->updateRules);

        return array_merge_recursive(
            $this->getRules(),
            $rules
        );
    }

    /**
     * Create rules ready array
     *
     * @param array $rules
     *
     * @return array
     */
    protected function createRulesArray($rules) : array
    {
        // If the array is not list, probably the user added array validation
        // rules e.q. '*.key' => 'required', in this case, we will make sure to include them
        if (! array_is_list($rules)) {
            $groups = collect($rules)->mapToGroups(function ($rules, $wildcard) {
                // If the $wildcard is integer, this means that it's a rule for the main field attribute
                // e.q. ['array', '*.key' => 'required']
                return [is_int($wildcard) ? 'attribute': 'wildcard' => [$wildcard => $rules]];
            })->all();

            return array_merge(
                [$this->requestAttribute() => $groups['attribute']->flatten()->all()],
                $groups['wildcard']->mapWithKeys(function ($rules) {
                    $wildcard = array_key_first($rules);

                    return [$this->requestAttribute() . '.' . $wildcard => Arr::wrap($rules[$wildcard])];
                })->all()
            );
        }

        return [
            $this->requestAttribute() => $rules,
        ];
    }

    /**
     * Set field custom validation error messages
     *
     * @param array $messages
     *
     * @return static
     */
    public function validationMessages(array $messages) : static
    {
        $this->validationMessages = $messages;

        return $this;
    }

    /**
     * Get the field validation messages
     *
     * @return array
     */
    public function prepareValidationMessages() : array
    {
        return collect($this->validationMessages)->mapWithKeys(function ($message, $rule) {
            return [$this->requestAttribute() . '.' . $rule => $message];
        })->all();
    }

    /**
     * Set whether to exclude the field from Zapier response
     *
     * @return static
     */
    public function excludeFromZapierResponse() : static
    {
        $this->excludeFromZapierResponse = true;

        return $this;
    }

    /**
     * Set the field custom field model
     *
     * @param \App\Innoclapps\Models\CustomField $field
     *
     * @return static
     */
    public function setCustomField(?CustomField $field) : static
    {
        $this->customField = $field;

        return $this;
    }

    /**
     * Check whether the current field is a custom field
     *
     * @return boolean
     */
    public function isCustomField() : bool
    {
        return ! is_null($this->customField);
    }

    /**
     * Get the field request attribute
     *
     * @return string
     */
    public function requestAttribute()
    {
        return $this->requestAttribute ?? $this->attribute;
    }

    /**
     * Create the field attributes for storage for the given request
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     * @param string $requestAttribute
     *
     * @return array|callable
     */
    public function storageAttributes(ResourceRequest $request, $requestAttribute) : array|callable
    {
        if (is_callable($this->saveUsing)) {
            return call_user_func_array($this->saveUsing, [
                $request,
                $requestAttribute,
                $this->attributeFromRequest($request, $requestAttribute),
                $this,
            ]);
        }

        return [
            $this->attribute => $this->attributeFromRequest($request, $requestAttribute),
        ];
    }

    /**
     * Get the field value for the given request
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     * @param string $requestAttribute
     *
     * @return mixed
     */
    public function attributeFromRequest(ResourceRequest $request, $requestAttribute) : mixed
    {
        return $request->exists($requestAttribute) ? $request[$requestAttribute] : null;
    }

    /**
     * Add custom attributes provider callback when creating/updating
     *
     * @param callable $callable
     *
     * @return static
     */
    public function saveUsing(callable $callable) : static
    {
        $this->saveUsing = $callable;

        return $this;
    }

    /**
     * Check whether the field is optionable
     *
     * @return boolean
     */
    public function isOptionable() : bool
    {
        if ($this->isMultiOptionable()) {
            return true;
        }

        return $this instanceof Optionable;
    }

    /**
     * Check whether the field is not optionable
     *
     * @return boolean
     */
    public function isNotOptionable() : bool
    {
        return ! $this->isOptionable();
    }

    /**
     * Check whether the field is multi optionable
     *
     * @return boolean
     */
    public function isMultiOptionable() : bool
    {
        return $this instanceof HasMany || $this instanceof MultiSelect || $this instanceof Checkbox;
    }

    /**
     * Check whether the field is not multi optionable
     *
     * @return boolean
     */
    public function isNotMultiOptionable() : bool
    {
        return ! $this->isMultiOptionable();
    }

    /**
     * Serialize for front end
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        // Determine if the field is required and then clear import status when mapping
        $isRequired = $this->isRequired(resolve(ResourceRequest::class));

        if (Innoclapps::isImportMapping()) {
            Innoclapps::setImportStatus(false);
        }

        return array_merge([
            'component'             => $this->component(),
            'attribute'             => $this->attribute,
            'label'                 => $this->label,
            'helpText'              => $this->helpText,
            'helpTextDisplay'       => $this->helpTextDisplay,
            'readonly'              => $this->isReadOnly(),
            'supportsInputGroup'    => $this->supportsInputGroup(),
            'collapsed'             => $this->collapsed,
            'primary'               => $this->isPrimary(),
            'icon'                  => $this->icon,
            'showOnIndex'           => $this->showOnIndex,
            'showOnCreation'        => $this->showOnCreation,
            'showOnUpdate'          => $this->showOnUpdate,
            'showOnDetail'          => $this->showOnDetail,
            'applicableForIndex'    => $this->isApplicableForIndex(),
            'applicableForCreation' => $this->isApplicableForCreation(),
            'applicableForUpdate'   => $this->isApplicableForUpdate(),
            'toggleable'            => $this->toggleable,
            'displayNone'           => $this->displayNone,
            'emitChangeEvent'       => $this->emitChangeEvent,
            'colClass'              => $this->getColClass(resolve(ResourceRequest::class)),
            'value'                 => $this->defaultValue(resolve(ResourceRequest::class)),
            'isRequired'            => $isRequired,
            'customField'           => $this->isCustomField() ? new CustomFieldResource($this->customField) : null,
        ], $this->meta());
    }
}
