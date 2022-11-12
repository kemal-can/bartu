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

namespace App\Http\View\FrontendComposers;

use JsonSerializable;

class Template implements JsonSerializable
{
    /**
     * @var \App\Http\View\FrontendComposers\Component|null
     */
    public ?Component $viewComponent = null;

    /**
     * Set the view component instance
     *
     * @param \App\Http\View\FrontendComposers\Component $component
     *
     * @return static
     */
    public function viewComponent(Component $component) : static
    {
        $this->viewComponent = $component;

        return $this;
    }

    /**
     * Prepare the template for front-end
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return [
            'view' => $this->viewComponent,
        ];
    }
}
