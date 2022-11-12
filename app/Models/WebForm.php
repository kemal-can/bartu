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

namespace App\Models;

use App\Enums\WebFormSection;
use App\Innoclapps\Models\Model;
use App\Innoclapps\Concerns\HasUuid;
use App\Innoclapps\Facades\Innoclapps;
use App\Innoclapps\Concerns\HasCreator;
use App\Innoclapps\Fields\FieldsCollection;
use App\Innoclapps\Contracts\Fields\Dateable;
use App\Contracts\Repositories\StageRepository;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Contracts\Repositories\PipelineRepository;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WebForm extends Model
{
    use HasCreator,
        HasFactory,
        HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'status',
        'sections',
        'notifications',
        'styles',
        'submit_data',
        'title_prefix',
        'locale',
        'user_id',
    ];

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'sections'          => 'array',
        'notifications'     => 'array',
        'styles'            => 'array',
        'submit_data'       => 'array',
        'total_submissions' => 'int',
        'user_id'           => 'int',
        'created_by'        => 'int',
    ];

    /**
     * Get all the web form deals
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deals()
    {
        return $this->hasMany(\App\Models\Deal::class);
    }

    /**
     * Get the web form owner
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    /**
     * Get the web form public URL
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function publicUrl() : Attribute
    {
        return Attribute::get(fn () => route('webform.view', $this->uuid));
    }

    /**
     * Get the form submit data attribute
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function submitData() : Attribute
    {
        return Attribute::get(function ($value) {
            $value = json_decode($value ?? '[]', true);

            $pipelineRepository = resolve(PipelineRepository::class);
            $stageRepository = resolve(StageRepository::class);

            if (! isset($value['pipeline_id']) || $pipelineRepository->count(['id' => $value['pipeline_id']]) === 0) {
                $pipeline = $pipelineRepository->findPrimary();
                $value['pipeline_id'] = $pipeline->getKey();
                $value['stage_id'] = $pipeline->stages->sortBy('display_order')->first()->getKey();
            } elseif ($stageRepository->count(['id' => $value['stage_id']]) === 0) {
                $pipeline = $pipelineRepository->find($value['pipeline_id']);
                $value['stage_id'] = $pipeline->stages->sortBy('display_order')->first()->getKey();
            }

            return $value;
        });
    }

    /**
     * Find field by resource
     *
     * @param string $attribute
     * @param string $resourceName
     *
     * @return \App\Innoclapps\Fields\Field|null
     */
    public function fieldByResource($attribute, $resourceName)
    {
        return $this->fields()->first(function ($field) use ($attribute, $resourceName) {
            return $field->meta()['resourceName'] === $resourceName && $field->attribute === $attribute;
        });
    }

    /**
     * Find section
     *
     * @param \App\Enums\WebFormSection $name
     *
     * @return array
     */
    public function sections(WebFormSection $name) : array
    {
        $sections = [];

        foreach ($this->sections as $section) {
            if (strtolower($section['type']) === strtolower($name->value)) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    /**
     * Get all of the form file sections
     *
     * @return array
     */
    public function fileSections() : array
    {
        return $this->sections(WebFormSection::FILE);
    }

    /**
     * Get all of the form field sections
     *
     * @return array
     */
    public function fieldSections() : array
    {
        return $this->sections(WebFormSection::FIELD);
    }

    /**
     * Get the form submit section
     *
     * @return array|null
     */
    public function submitSection() : ?array
    {
        return $this->sections(WebFormSection::SUBMIT)[0] ?? null;
    }

    /**
     * Get the form introduction section
     *
     * @return array|null
     */
    public function introductionSection() : ?array
    {
        return $this->sections(WebFormSection::INTRODUCTION)[0] ?? null;
    }

    /**
     * Get the web form fields
     *
     * @return \App\Innoclapps\Fields\FieldsCollection
     */
    public function fields() : FieldsCollection
    {
        return once(function () {
            $fields = new FieldsCollection([]);

            foreach ($this->fieldSections() as $section) {
                // Use getFields instead of resolveFields as the forms are available to admin
                // in case admin decide to add some field that canSee return false
                // won't be available here or in the request when resolving the fields
                $field = Innoclapps::resourceByName(
                    $section['resourceName']
                )->getFields()->find($section['attribute']);

                // Field removed?
                if (! $field) {
                    continue;
                }

                if ($field->isUnique()) {
                    $field->notUnique();
                }

                $field->requestAttribute = $section['requestAttribute'];

                // For front-end
                $field->withMeta([
                        'attribute' => $section['requestAttribute'],
                        // Used in repository to find the field by resource
                        'resourceName' => $section['resourceName'],
                    ])
                    ->label($section['label'])
                    ->help('')
                    ->canSee(fn () => true);

                if ($section['isRequired']) {
                    $field->rules('required');

                    if ($field->isOptionable()) {
                        $field->withMeta(['attributes' => ['clearable' => false]]);
                    }
                }

                // if ($field instanceof Dateable) {
                //     $field->withMeta(['attributes' => [
                //            'color' => $this->styles['primary_color'],
                //         ]]);
                // }

                $fields->push($field);
            }

            return $fields;
        });
    }

    /**
     * Check whether the form is active
     *
     * @return boolean
     */
    public function isActive() : bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the form status.
     */
    protected function status() : Attribute
    {
        return new Attribute(
            get: fn ($value) => (int) $value === 0 ? 'inactive' : 'active',
            set: fn ($value) => $value === 'active' ? 1 : 0
        );
    }
}
