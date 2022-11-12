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

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use App\Innoclapps\Workflow\Workflows;
use Illuminate\Foundation\Http\FormRequest;
use App\Innoclapps\Contracts\Workflow\FieldChangeTrigger;

class WorkflowRequest extends FormRequest
{
    /**
     * Trigger instance
     *
     * @var \App\Innoclapps\Workflows\Trigger
     */
    protected $trigger;

    /**
     * Create properly formatted data for storage
     *
     * @return array
     */
    public function createData()
    {
        return array_merge_recursive(
            $this->only(['trigger_type', 'action_type', 'title', 'description', 'is_active']),
            // Get the action available fields values
            ['data' => array_merge(
                $this->only($this->fieldsAttributes()),
                $this->isFieldChangeTrigger() ?
                    value(function ($changeField) {
                        return [
                            $changeField->attribute => $this->{$changeField->attribute},
                        ];
                    }, $this->getTrigger()::changeField()) : []
            )],
            ['created_by' => $this->user()->id]
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_merge(
            [
                'trigger_type' => ['required', Rule::in(Workflows::availableTriggers())],
                'action_type'  => ['required', function ($attribute, $value, $fail) {
                    if (! $this->getTrigger()) {
                        return;
                    }

                    if (is_null($this->getTrigger()->getAction($value))) {
                        $fail(__('validation.in_array', [
                            'attribute' => 'action',
                            'other'     => 'the trigger available actions',
                        ]));
                    }
                }],
                'title'       => 'required|string|max:191',
                'description' => 'max:191',
                'is_active'   => 'boolean',
            ],
            $this->isFieldChangeTrigger() ? [$this->getTrigger()::changeField()->attribute => 'required'] : [],
            $this->getRulesFromActionFields()
        );
    }

    /**
     * Get the rules from the action fields
     *
     * @return array
     */
    protected function getRulesFromActionFields() : array
    {
        if (! $this->getTrigger()) {
            return [];
        }

        return $this->actionFields()->mapWithKeys(fn ($field) => $field->getRules())->all();
    }

    /**
     * Get the trigger for the request
     *
     * @return \App\Innoclapps\Workflow\Trigger|\App\Innoclapps\Contracts\Workflow\FieldChangeTrigger|null
     */
    public function getTrigger()
    {
        if (! $this->trigger && $this->trigger_type && in_array($this->trigger_type, Workflows::availableTriggers())) {
            $this->trigger = Workflows::newTriggerInstance($this->trigger_type);
        }

        return $this->trigger;
    }

    /**
     * Check whether the trigger is field change
     *
     * @return boolean
     */
    public function isFieldChangeTrigger()
    {
        return $this->getTrigger() instanceof FieldChangeTrigger;
    }

    /**
     * Get the action fields
     *
     * @return \Illuminate\Support\Collection
     */
    public function actionFields()
    {
        $fields = collect([]);

        if (! $this->action_type) {
            return $fields;
        }

        if ($action = $this->getTrigger()->getAction($this->action_type)) {
            $fields = $fields->merge($action->fields());
        }

        return $fields;
    }

    /**
     * Get the action fields attributes
     *
     * @return array
     */
    public function fieldsAttributes()
    {
        return $this->actionFields()->map(fn ($field) => $field->requestAttribute())->all();
    }
}
