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

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Innoclapps\Rules\Locale;
use App\Http\Controllers\ApiController;
use App\Innoclapps\Translation\Translation;
use App\Innoclapps\Translation\DotNotationResult;
use App\Innoclapps\Contracts\Translation\TranslationLoader;

class TranslationController extends ApiController
{
    /**
     * Initialize new TranslationController instance.
     *
     * @param \App\Innoclapps\Contracts\Translation\TranslationLoader $translator
     */
    public function __construct(protected TranslationLoader $translator)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @param string $locale
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($locale)
    {
        return $this->response([
            'source'  => $this->translator->getOriginal($locale),
            'current' => Translation::current($locale),
        ]);
    }

    /**
     * Create new language locale
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $payload = $request->validate([
            'name' => [
                'required',
                'string',
                new Locale,
                Rule::notIn(Translation::availableLocales()),
            ],
        ]);

        $created = Translation::createNewLocale($payload['name']);

        return $this->response($created ? [
                'locale' => $payload['name'],
            ] : ['message' => 'Failed to create new locale.'], $created ? 201 : 500);
    }

    /**
     * Update locale group translations
     *
     * @param string $locale
     * @param string $group
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($locale, $group, Request $request)
    {
        // Save translations flag

        $this->translator->saveTranslations(
            $locale,
            $group,
            new DotNotationResult($request->all())
        );

        return $this->response('', 204);
    }
}
