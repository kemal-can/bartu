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

namespace App\Innoclapps\Media;

use Illuminate\Support\Str;
use Plank\Mediable\Mediable;

trait HasMedia
{
    use Mediable;

    /**
     * Boot HasMedia trait
     *
     * @return void
     */
    protected static function bootHasMedia()
    {
        static::deleting(function ($model) {
            if (! $model->usesSoftDeletes() || $model->isForceDeleting()) {
                $model->purgeMedia();
            }
        });
    }

    /**
     * Purge the model media
     *
     * The function deletes the files and the storage folder when empty
     *
     * @return void
     */
    public function purgeMedia() : void
    {
        $this->media()->get()->each->delete();
    }

    /**
     * Get the model media directory
     *
     * @return string
     */
    public function getMediaDirectory() : string
    {
        $folder = Str::kebab(class_basename(get_called_class()));

        return config('innoclapps.media.directory') . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $this->id;
    }

    /**
     * Get the model media tag
     *
     * @return array
     */
    public function getMediaTags() : array
    {
        return ['profile'];
    }
}
