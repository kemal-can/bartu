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

namespace App\Support\Fields;

use App\Enums\PhoneType;
use Illuminate\Support\Str;
use App\Models\Phone as Model;
use App\Support\CountryCallingCode;
use App\Innoclapps\Fields\MorphMany;
use App\Http\Resources\PhoneResource;
use App\Innoclapps\Table\MorphManyColumn;
use Illuminate\Contracts\Validation\Validator;
use App\Contracts\Repositories\PhoneRepository;

class Phone extends MorphMany
{
    /**
     * Phone types
     *
     * @var array
     */
    public $types = [];

    /**
     * Default type
     *
     * @var \App\Enums\PhoneType|null
     */
    public $type;

    /**
     * Field component
     *
     * @var string
     */
    public $component = 'phone-field';

    /**
     * Display key
     *
     * @var string
     */
    public $displayKey = 'number';

    /**
     * Calling prefix
     *
     * @var mixed
     */
    public $callingPrefix = null;

    /**
     * Indicates whether the phone should be unique
     *
     * @var \Illuminate\Database\Eloquent\Model|bool
     */
    public $unique = false;

    /**
     * Indicates whether to skip the unique rule validation in import
     *
     * @var boolean
     */
    public $uniqueRuleSkipOnImport = true;

    /**
     * Unique rule custom validation message
     *
     * @var string
     */
    public $uniqueRuleMessage;

    /**
     * Provide the column used for index
     *
     * @return \App\Innoclapps\Table\HasManyColumn
     */
    public function indexColumn() : MorphManyColumn
    {
        return tap(new MorphManyColumn(
            $this->morphManyRelationship,
            $this->displayKey,
            $this->label
        ), function ($column) {
            $column->select('type')
                ->useComponent('table-data-phones');
        });
    }

    /**
     * Mark the field as unique
     *
     * @param string $model
     * @param string $message
     *
     * @return static
     */
    public function unique($model, $message = 'The phone number already exists', $skipOnImport = true) : static
    {
        $this->unique                 = $model;
        $this->uniqueRuleMessage      = $message;
        $this->uniqueRuleSkipOnImport = $skipOnImport;

        return $this;
    }

    /**
     * Resolve the field value for import
     *
     * @param string|null $value
     * @param array $row
     * @param array $original
     *
     * @return array|null
     */
    public function resolveForImport($value, $row, $original)
    {
        return $this->addCallingPrefixIfNeeded(
            parent::resolveForImport($value, $row, $original)
        );
    }

    /**
     * Set the phone types
     *
     * @param array $types
     *
     * @return static
     */
    public function types($types)
    {
        $this->types = $types;

        return $this;
    }

    /**
     * Set the default phone type
     *
     * @param \App\Enums\PhoneType $type
     *
     * @return static
     */
    public function defaultType(PhoneType $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Handle the phone field validation
     *
     * @param array $value
     * @param \Illuminate\Contracts\Validation\Validator $validator
     *
     * @return void
     */
    protected function validate(&$value, Validator $validator)
    {
        foreach ($value as $key => $number) {
            $value[$key]['_track_by'] = ['number' => $number['number']];

            if (empty($number['number']) && isset($number['id'])) {
                $value[$key]['_delete'] = true;
            } elseif ($this->callingPrefix() && ! CountryCallingCode::startsWithAny($number['number'])) {
                $this->addValidationError($validator, $key, __('validation.calling_prefix', ['attribute' => $this->label]));
            }

            if ($this->unique && ! empty($number['number']) && ! $this->isNumberUnique($number)) {
                $this->addValidationError($validator, $key, $this->uniqueRuleMessage);
            }
        }
    }

    /**
     * Get the phone field calling prefix
     *
     * @return string|bool|null
     */
    public function callingPrefix()
    {
        // Import multiple calls protection
        return once(function () {
            $prefix = $this->callingPrefix;

            if ($prefix instanceof \Closure) {
                $prefix = $prefix();
            }

            if ($prefix) {
                if ($prefix !== true && ! Str::startsWith($prefix, '+')) {
                    $prefix = '+' . $prefix;
                }

                return $prefix;
            }
        });
    }

    /**
     * Add calling prefix
     *
     * @param mixed $prefix
     *
     * @return static
     */
    public function requireCallingPrefix($default = true)
    {
        $this->callingPrefix = $default;

        return $this;
    }

    /**
     * Indicates that the relation will be counted
     *
     * @return static
     */
    public function count() : static
    {
        throw new \Exception('The ' . class_basename(__CLASS__) . ' field does not support counting.');
    }

    /**
     * Add validation error
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @param int $key
     * @param string $message
     */
    protected function addValidationError($validator, $key, $message)
    {
        $validator->after(function ($validator) use ($key, $message) {
            $validator->errors()->add($this->requestAttribute() . '.' . $key . '.number', $message);
        });
    }

    /**
     * Check whether the given number is unique
     *
     * @param array $number
     *
     * @return boolean
     */
    protected function isNumberUnique($number)
    {
        $where = ['number' => $number['number'], 'phoneable_type' => $this->unique];

        if (isset($number['id'])) {
            $where[] = ['id', '!=', $number['id']];
        }

        return resolve(PhoneRepository::class)->count($where, ['id']) === 0;
    }

    /**
     * If needed, add calling prefix to the given value
     *
     * @param array $value
     *
     * @return array
     */
    protected function addCallingPrefixIfNeeded($value)
    {
        $prefix = $this->callingPrefix();

        if (! $prefix || $prefix === true) {
            return $value;
        }

        foreach ($value[$this->attribute] ?? [] as $key => $phone) {
            if (empty($phone[$this->displayKey]) ||
                    CountryCallingCode::startsWithAny($phone[$this->displayKey])) {
                continue;
            }

            if (! Str::startsWith($prefix, $phone[$this->displayKey])) {
                $value[$this->attribute][$key][$this->displayKey] = $prefix . $value[$this->attribute][$key][$this->displayKey];
            }
        }

        return $value;
    }

    /**
     * Boot the field
     *
     * @return void
     */
    public function boot()
    {
        $this->types(collect(PhoneType::names())->mapWithKeys(function ($name) {
            return [$name => __('fields.phones.types.' . $name)];
        })->all())
            ->defaultType(PhoneType::mobile)
            ->setJsonResource(PhoneResource::class)
            ->prepareForValidation(function ($value, $request, $validator, $data) {
                // Allow providing the phone number as string, will use the default phone type
                if (! is_array($value) && ! empty($value)) {
                    $value = [['number' => $value]];
                }

                if (is_array($value)) {
                    $this->validate($value, $validator);
                }

                return $value;
            })->provideSampleValueUsing(function () {
                return [['number' => Model::generateRandomPhoneNumber(), 'type' => array_rand($this->types)]];
            })->provideImportValueSampleUsing(function () {
                return implode(',', [Model::generateRandomPhoneNumber(), Model::generateRandomPhoneNumber()]);
            })->saveUsing(function ($request, $requestAttribute, $value, $field) {
                foreach ($value ?? [] as $key => $phone) {
                    if (isset($phone['type'])) {
                        $value[$key]['type'] = PhoneType::find($phone['type']) ?? $this->type;
                    }

                    // ResourceFulHandlerWithFields will delete the phone when _delete exists
                    if (empty($phone['number']) && ! isset($phone['_delete'])) {
                        unset($value[$key]);
                    }
                }

                return [$field->attribute => $value];
            });
    }

    /**
     * Generate random phone digits
     *
     * @param integer $digits
     *
     * @return int
     */
    protected function randomPhoneDigits($digits = 3)
    {
        return rand(pow(10, $digits - 1), pow(10, $digits) - 1);
    }

    /**
     * jsonSerialize
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return array_merge(parent::jsonSerialize(), [
            'types'         => $this->types,
            'type'          => $this->type?->name,
            'callingPrefix' => value(function () {
                return $this->callingPrefix() === true ? null : $this->callingPrefix();
            }),
        ]);
    }
}
