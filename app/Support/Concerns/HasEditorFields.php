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

namespace App\Support\Concerns;

use App\Innoclapps\EditorImagesProcessor;

trait HasEditorFields
{
    /**
     * Boot HasEditorFields trait
     *
     * @return void
     */
    protected static function bootHasEditorFields()
    {
        static::updated(function ($model) {
            static::runEditorImagesProcessor($model);
        });

        static::created(function ($model) {
            static::runEditorImagesProcessor($model);
        });

        static::deleted(function ($model) {
            if (! $model->usesSoftDeletes() || $model->isForceDeleting()) {
                static::createEditorImagesProcessor()->deleteAllViaModel(
                    $model,
                    $model->getEditorFields()
                );
            }
        });
    }

    /**
     * Get the fields that are used as editor
     *
     * @return array|string
     */
    abstract public function getEditorFields() : array|string;

    /**
     * Run the editor images processor
     *
     * @param $this $model
     *
     * @return void
     */
    protected static function runEditorImagesProcessor($model)
    {
        static::createEditorImagesProcessor()->processViaModel(
            $model,
            $model->getEditorFields()
        );
    }

    /**
     * Create editor images processor
     *
     * @return \App\Innoclapps\EditorImagesProcessor
     */
    protected static function createEditorImagesProcessor() : EditorImagesProcessor
    {
        return new EditorImagesProcessor();
    }
}
