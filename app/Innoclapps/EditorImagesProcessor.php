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

namespace App\Innoclapps;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use KubAT\PhpSimple\HtmlDomParser;
use App\Innoclapps\Contracts\Repositories\MediaRepository;
use App\Innoclapps\Contracts\Repositories\PendingMediaRepository;

class EditorImagesProcessor
{
    /**
     * The media directory where the pending media
     * will be moved after save/create
     *
     * @var string
     */
    protected static string $mediaDir = 'editor';

    /**
     * @var \App\Innoclapps\Contracts\Repositories\MediaRepository
     */
    protected MediaRepository $media;

    /**
     * @var \App\Innoclapps\Contracts\Repositories\PendingMediaRepository
     */
    protected PendingMediaRepository $pendingMedia;

    /**
     * Initialize new EditorImagesProcessor instance.
     */
    public function __construct()
    {
        $this->media        = resolve(MediaRepository::class);
        $this->pendingMedia = resolve(PendingMediaRepository::class);
    }

    /**
     * Process editor fields by given new and original content
     *
     * @param string $newContent
     * @param string $originalContent
     *
     * @return void
     */
    public function process($newContent, $originalContent) : void
    {
        // First store the current media tokens
        $tokens = $this->getMediaTokensFromContent($newContent);

        // After that only process the new images
        $this->processNewImages($tokens);

        // Finaly removed any removed images
        $this->media->deleteByTokens(
            $this->getRemovedImages($originalContent, $tokens)
        );
    }

    /**
     * Process editor images via given model
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array|string $field
     *
     * @return void
     */
    public function processViaModel($model, array|string $fields) : void
    {
        foreach (Arr::wrap($fields) as $field) {
            // First store the current media tokens
            $tokens = $this->getMediaTokensFromContent($model->{$field});

            // After that only process the new images
            $this->processNewImages($tokens);

            // Check if it's update, if yes, get the removed images tokens
            // and delete them
            if (! $model->wasRecentlyCreated) {
                $this->media->deleteByTokens(
                    $this->getRemovedImages($model->getOriginal($field), $tokens)
                );
            }
        }
    }

    /**
     * Delete all images via model
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array|string $fields
     *
     * @return void
     */
    public function deleteAllViaModel($model, array|string $fields) : void
    {
        foreach (Arr::wrap($fields) as $field) {
            $this->deleteAllByContent($model->{$field});
        }
    }

    /**
     * Delete images by given content
     *
     * @return void
     */
    public function deleteAllByContent($content) : void
    {
        $this->media->deleteByTokens(
            $this->getMediaTokensFromContent($content)
        );
    }

    /**
     * Process the new images by given tokens
     *
     * @param array $tokens
     *
     * @return void
     */
    protected function processNewImages($tokens) : void
    {
        // Handle all pending medias and move them to the appropriate
        // directory and also delete the pending record from the pending table after move
        // From the current, we will get only the pending which are not yet processed
        $this->pendingMedia->getByToken($tokens)->each(function ($pending) {
            $pending->attachment->move(static::$mediaDir, Str::random(40));
            $this->pendingMedia->delete($pending->id);
        });
    }

    /**
     * Get the removed images from the editor content
     *
     * @param string|array|null $originalContent
     * @param string|array $newContent
     *
     * @return array
     */
    protected function getRemovedImages($originalContent, $newContent) : array
    {
        if (is_null($originalContent)) {
            return [];
        }

        return array_diff(
            is_string($originalContent) ? $this->getMediaTokensFromContent($originalContent) : $originalContent,
            is_string($newContent) ? $this->getMediaTokensFromContent($newContent) : $newContent
        );
    }

    /**
     * Get the media current token via the images
     *
     * @param string $content
     *
     * @return array
     */
    protected function getMediaTokensFromContent($content) : array
    {
        $sources = [];

        if (! $dom = HtmlDomParser::str_get_html($content)) {
            return $sources;
        }

        foreach ($dom->find('img') as $element) {
            if ($src = $element->getAttribute('src')) {
                // Check if is really media by checking the uuid in the image src
                preg_match('/[\da-f]{8}-[\da-f]{4}-[\da-f]{4}-[\da-f]{4}-[\da-f]{12}/m', $src, $matches);

                if (count($matches) === 1) {
                    $sources[] = $matches[0];
                }
            }
        }

        return $sources;
    }
}
