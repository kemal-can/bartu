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

namespace App\Support\Highlights;

use JsonSerializable;

abstract class Highlight implements JsonSerializable
{
    /**
     * Get the highlight name
     *
     * @return string
     */
    abstract public function name() : string;

    /**
     * Get the highligh count
     *
     * @return integer
     */
    abstract public function count() : int;

    /**
     * Get the background color class when the highligh count is bigger then zero
     *
     * @return string
     */
    abstract public function bgColorClass() : string;

    /**
     * Get the router to
     *
     * @return array|string
     */
    abstract public function to() : array|string;

    /**
     * Prepare the class for JSON serialization
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return [
            'count'        => $this->count(),
            'name'         => $this->name(),
            'to'           => $this->to(),
            'bgColorClass' => $this->bgColorClass(),
        ];
    }
}
