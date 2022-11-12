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

use Exception;
use App\Innoclapps\Fields\Field;
use App\Innoclapps\Table\Column;
use App\Contracts\Repositories\NoteRepository;

class ImportNote extends Field
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = null;

    /**
     * Initialize new ImportNote instance class
     *
     * @param string $attribute field attribute
     * @param string|null $label field label
     */
    public function __construct($attribute = 'import_note', $label = null)
    {
        parent::__construct($attribute, $label ?: __('note.note'));

        $this->strictlyForImport()
            ->excludeFromZapierResponse()
            ->saveUsing(function ($request, $requestAttribute, $value, $field) {
                return function ($model) use ($request, $value) {
                    if (empty($value)) {
                        return;
                    }

                    $notes = resolve(NoteRepository::class);

                    if (! $model->notes()->where('body', $value)->exists()) {
                        $notes->attach($notes->create([
                            'body'            => $value,
                            'via_resource'    => $request->resource()->name(),
                            'via_resource_id' => $model->getKey(),
                        ])->getKey(), $request->resource()->associateableName(), [$model->getKey()]);
                    }
                };
            });
    }

    /**
     * Get the mailable template placeholder
     *
     * @param \App\Innoclapps\Models\Model $model
     *
     * @return null
     */
    public function mailableTemplatePlaceholder($model)
    {
        return null;
    }

    /**
     * Provide the column used for index
     *
     * @return null
     */
    public function indexColumn() : ?Column
    {
        return null;
    }

    /**
    * Resolve the actual field value
    *
    * @param \Illuminate\Database\Eloquent\Model $model
    *
    * @return mixed
    */
    public function resolve($model)
    {
        return null;
    }

    /**
     * Resolve the displayable field value
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return null
     */
    public function resolveForDisplay($model)
    {
        return null;
    }

    /**
     * Resolve the field value for export
     *
     * @param \App\Innoclapps\Models\Model $model
     *
     * @return null
     */
    public function resolveForExport($model)
    {
        return null;
    }

    /**
     * Resolve the field value for import
     *
     * @param string|null $value
     * @param array $row
     * @param array $original
     *
     * @return null
     */
    public function resolveForImport($value, $row, $original)
    {
        return null;
    }

    /**
     * Resolve the field value for JSON Resource
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return null
     */
    public function resolveForJsonResource($model)
    {
        return null;
    }

    /**
     * Add custom value resolver
     *
     * @param callable $resolveCallback
     *
     * @return static
     */
    public function resolveUsing(callable $resolveCallback)
    {
        throw new Exception(__CLASS__ . ' cannot have custom resolve callback');
    }

    /**
     * Add custom display resolver
     *
     * @param callable $displayCallback
     *
     * @return static
     */
    public function displayUsing(callable $displayCallback)
    {
        throw new Exception(__CLASS__ . ' cannot have custom display callback');
    }
}
