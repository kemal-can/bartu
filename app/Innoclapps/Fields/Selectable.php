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

trait Selectable
{
    /**
     * Set async URL for searching
     *
     * @param string $asyncUrl
     *
     * @return static
     */
    public function async($asyncUrl) : static
    {
        $this->withMeta(['asyncUrl' => $asyncUrl]);

        // Automatically add placeholder "Type to search..." on async fields
        $this->withMeta(['attributes' => ['placeholder' => __('app.type_to_search')]]);

        return $this;
    }
}
