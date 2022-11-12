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

namespace App\Innoclapps\Fields;

use App\Innoclapps\Resources\Http\ResourceRequest;

class Domain extends Field
{
    /**
     * Field component
     *
     * @var string
     */
    public $component = 'domain-field';

    /**
     * This field support input group
     *
     * @var boolean
     */
    public bool $supportsInputGroup = true;

    /**
     * Boot field
     *
     * Sets icon
     *
     * @return null
     */
    public function boot()
    {
        $this->provideSampleValueUsing(fn () => 'example.com')->prependIcon('Globe');
    }

    /**
     * Get the field value for the given request
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     * @param string $requestAttribute
     *
     * @return mixed
     */
    public function attributeFromRequest(ResourceRequest $request, $requestAttribute) : mixed
    {
        $value = parent::attributeFromRequest($request, $requestAttribute);

        return \App\Innoclapps\Domain::extractFromUrl($value ?? '');
    }
}
