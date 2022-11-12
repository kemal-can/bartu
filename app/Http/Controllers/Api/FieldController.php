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
use App\Innoclapps\Facades\Fields;
use App\Http\Controllers\ApiController;

class FieldController extends ApiController
{
    /**
     * Get fields in a group for specific view for display
     *
     * @param string $group Fields group/feature
     * @param string $view Fields view e.q. create|update
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($group, $view)
    {
        return $this->response(
            Fields::resolveForDisplay($group, $view)
        );
    }

    /**
     * Get the fields that are intended for the settings
     *
     * @param string $group Fields group/feature
     * @param string $view Fields view (create|update)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function settings($group, $view)
    {
        return $this->response(
            Fields::resolveForSettings($group, $view)
        );
    }

    /**
     * Get fields for settings in bulk in given groups
     *
     * @param string $view
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkSettings($view, Request $request)
    {
        return $this->response(
            collect($request->get('groups', []))->mapWithKeys(
                fn ($group) => [$group => Fields::resolveForSettings($group, $view)]
            )
        );
    }

    /**
     * Save the customized fields from settings
     *
     * @param string $group Fields group/feature
     * @param string $view Fields view (create|update)
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($group, $view, Request $request)
    {
        Fields::customize($request->post(), $group, $view);

        return $this->response(
            Fields::resolveForDisplay($group, $view)
        );
    }

    /**
     * Reset the customized fields for a view
     *
     * @param string $group Fields group/feature
     * @param string $view Fields view (create|update)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($group, $view)
    {
        Fields::customize([], $group, $view);

        return $this->response([
            'settings' => Fields::resolveForSettings($group, $view),
            'fields'   => Fields::resolveForDisplay($group, $view),
        ]);
    }
}
