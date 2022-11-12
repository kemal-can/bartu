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

class Section
{
    use Makeable;

    /**
     * Indicates whether the section is enabled
     *
     * @var boolean
     */
    public bool $enabled = true;

    /**
     * Section order
     *
     * @var integer|null
     */
    public ?int $order = null;

    /**
     * Section heading
     *
     * @var string|null
     */
    public ?string $heading = null;

    /**
     * Create new Section instance
     *
     * @param string $id
     * @param string $component
     */
    public function __construct(public string $id, public string $component)
    {
    }

    /**
     * Set the section heading
     *
     * @param string $title
     *
     * @return static
     */
    public function heading(string $heading) : static
    {
        $this->heading = $heading;

        return $this;
    }
}
