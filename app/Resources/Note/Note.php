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

namespace App\Resources\Note;

use Illuminate\Http\Request;
use App\Http\Resources\NoteResource;
use App\Innoclapps\Resources\Resource;
use App\Innoclapps\Criteria\RelatedCriteria;
use App\Contracts\Repositories\NoteRepository;
use App\Innoclapps\Contracts\Resources\Resourceful;

class Note extends Resource implements Resourceful
{
    /**
    * Get the underlying resource repository
    *
    * @return \App\Innoclapps\Repository\AppRepository
    */
    public static function repository()
    {
        return resolve(NoteRepository::class);
    }

    /**
    * Get the json resource that should be used for json response
    *
    * @return string
    */
    public function jsonResource() : string
    {
        return NoteResource::class;
    }

    /**
    * Get the criteria that should be used to fetch only own data for the user
    *
    * @return string|null
    */
    public function ownCriteria() : ?string
    {
        if (! auth()->user()->isSuperAdmin()) {
            return RelatedCriteria::class;
        }

        return null;
    }

    /**
    * Get the resource relationship name when it's associated
    *
    * @return string
    */
    public function associateableName() : string
    {
        return 'notes';
    }

    /**
    * Get the relations to eager load when quering associated records
    *
    * @return array
    */
    public function withWhenAssociated() : array
    {
        return ['user'];
    }

    /**
     * Get the countable relations when quering associated records
     *
     * @return array
     */
    public function withCountWhenAssociated() : array
    {
        return ['comments'];
    }

    /**
    * Get the resource rules available for create and update
    *
    * @param \Illuminate\Http\Request $request
    *
    * @return array
    */
    public function rules(Request $request)
    {
        return [
            'body'            => 'required|string',
            'via_resource'    => 'required|string',
            'via_resource_id' => 'required|numeric',
        ];
    }

    /**
     * Get the custom validation messages for the resource
     * Useful for resources without fields.
     *
     * @return array
     */
    public function validationMessages() : array
    {
        return [
            'body.required' => __('validation.required_without_label'),
        ];
    }
}
