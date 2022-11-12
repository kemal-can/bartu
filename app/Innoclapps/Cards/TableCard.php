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

namespace App\Innoclapps\Cards;

class TableCard extends Card
{
    /**
     * The primary key for the table row
     *
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * Define the card component used on front end
     *
     * @return string
     */
    public function component() : string
    {
        return 'card-with-table';
    }

    /**
     * Provide the table fields
     *
     * @return array
     */
    public function fields() : array
    {
        return [];
    }

    /**
     * Provide the table data
     *
     * @return iterable
     */
    public function items() : iterable
    {
        return [];
    }

    /**
     * Table empty text
     *
     * @return string|null
     */
    public function emptyText() : ?string
    {
        return null;
    }

    /**
     * jsonSerialize
     *
     * @return array
     */
    public function jsonSerialize() : array
    {
        return array_merge(parent::jsonSerialize(), [
            'fields'     => $this->fields(),
            'items'      => $this->items(),
            'emptyText'  => $this->emptyText(),
            'primaryKey' => $this->primaryKey,
        ]);
    }
}
