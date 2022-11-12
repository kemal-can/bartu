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

namespace App\Innoclapps\Settings\Stores;

class ArrayStore extends AbstractStore
{
    /**
     * Fire the post options to customize the store.
     *
     * @param array $options
     */
    protected function postOptions(array $options)
    {
        // Do nothing...
    }

    /**
     * Read the data from the store.
     *
     * @return array
     */
    protected function read() : array
    {
        return $this->data;
    }

    /**
     * Write the data into the store.
     *
     * @param array $data
     *
     * @return void
     */
    protected function write(array $data) : void
    {
        // Nothing to do...
    }
}
