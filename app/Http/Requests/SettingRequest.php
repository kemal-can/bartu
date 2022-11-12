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

use Illuminate\Support\Str;
use App\Innoclapps\EditorImagesProcessor;
use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
{
    /**
     * The original settings values
     *
     * @var array
     */
    protected $originalValues;

    /**
     * Editorable fields
     *
     * @var array
     */
    protected $editor = ['privacy_policy'];

    /**
     * Save the settings via request
     *
     * @return void
     */
    public function saveSettings()
    {
        $this->processEditorFields();

        $required = default_setting()->getRequired();

        collect($this->all())
            ->reject(
                fn ($value, $name) => in_array($name, $required) && empty($value) || Str::startsWith($name, '_')
            )
            ->each(function ($value, $name) {
                is_null($value) ?
                settings()->forget($name) :
                settings()->set($name, $value);
            });

        settings()->save();
    }

    /**
     * Process the editor fields
     *
     * @return void
     */
    public function processEditorFields()
    {
        foreach ($this->all() as $name => $val) {
            if (in_array($name, $this->editor)) {
                (new EditorImagesProcessor)->process($val, $this->getOriginalValues($name) ?? '');
            }
        }
    }

    /**
     * Get the original settings values
     *
     * @param string|null $name
     *
     * @return array|string|null
     */
    public function getOriginalValues($name = null)
    {
        if (! is_null($this->originalValues)) {
            $settings = $this->originalValues;
        } else {
            $settings = $this->originalValues = settings()->all();
        }

        return $name ? ($settings[$name] ?? null) : $settings;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }
}
