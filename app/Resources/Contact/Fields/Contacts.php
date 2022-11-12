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

namespace App\Resources\Contact\Fields;

use App\Innoclapps\Fields\MorphToMany;
use App\Http\Resources\ContactResource;

class Contacts extends MorphToMany
{
    /**
     * Field order
     *
     * @var integer
     */
    public ?int $order = 1000;

    /**
     * Create new instance of Contacts field
     *s
     * @param string $companies
     * @param string $label Custom label
     */
    public function __construct($relation = 'contacts', $label = null)
    {
        parent::__construct($relation, $label ?? __('contact.contacts'));

        $this->setJsonResource(ContactResource::class)
            ->labelKey('display_name')
            ->valueKey('id')
            ->excludeFromExport()
            ->excludeFromImport()
            ->excludeFromZapierResponse()
            ->async('/contacts/search')
            ->tapIndexColumn(function ($column) {
                if (! $this->counts()) {
                    $column->useComponent('table-presentable-data-column');
                }
                // For display_name append
                $column->queryAs('first_name')->select(['last_name']);
            })->provideSampleValueUsing(fn () => [1, 2]);
    }
}
