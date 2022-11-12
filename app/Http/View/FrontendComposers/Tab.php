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

use App\Innoclapps\Makeable;

class Tab
{
    use Makeable;

    /**
     * @var array
     */
    public array $props = [];

    /**
     * Create new Tab instance.
     *
     * @param string $id
     * @param string $component
     */
    public function __construct(public string $id, public string $component)
    {
    }

    /**
     * Set custom tab props
     *
     * @param array $props
     *
     * @return static
     */
    public function props(array $props) : static
    {
        $this->props = $props;

        return $this;
    }
}
