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

trait ChangesKeys
{
    /**
     * From where the value key should be taken
     * @var string
     */
    public string $valueKey = 'value';

    /**
     * From where the label key should be taken
     * @var string
     */
    public string $labelKey = 'label';

    /**
     * Set custom key for value
     *
     * @param string $key
     *
     * @return mixed
     */
    public function valueKey(string $key) : static
    {
        $this->valueKey = $key;

        return $this;
    }

    /**
     * Set custom label key
     *
     * @param string $key
     *
     * @return mixed
     */
    public function labelKey(string $key) : static
    {
        $this->labelKey = $key;

        return $this;
    }
}
