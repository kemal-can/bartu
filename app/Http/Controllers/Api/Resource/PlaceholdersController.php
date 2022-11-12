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

namespace App\Http\Controllers\Api\Resource;

use Illuminate\Http\Request;
use App\Innoclapps\Facades\Innoclapps;
use App\Http\Controllers\ApiController;
use App\Innoclapps\Resources\MailPlaceholders;

class PlaceholdersController extends ApiController
{
    /**
     * Retrieve placeholders via fields
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        return $this->response(MailPlaceholders::createGroupsFromResources(
            $request->input('resources', [])
        ));
    }

    /**
     * Parse the given content via the given resources records
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function parse(Request $request)
    {
        $content = $request->content;

        collect($request->input('resources', []))->map(function ($resource) {
            $instance = Innoclapps::resourceByName($resource['name']);

            return $instance ? [
                'record'       => $record = $instance->displayQuery()->find($resource['id']),
                'resource'     => $instance,
                'placeholders' => new MailPlaceholders($instance, $record),
            ] : null;
        })->filter()
            ->reject(fn ($resource) => $request->user()->cant('view', $resource['record']))
            ->unique(fn ($resource) => $resource['resource']->name())
            ->each(function ($resource) use (&$content) {
                $content = $resource['placeholders']->parseWhenViaInputFields($content);
            });

        return $this->response($content);
    }
}
