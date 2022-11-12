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

namespace App\Resources\Contact\Cards;

use App\Innoclapps\Date\Carbon;
use App\Innoclapps\Cards\TableCard;
use App\Criteria\Contact\OwnContactsCriteria;
use App\Contracts\Repositories\ContactRepository;

class RecentlyCreatedContacts extends TableCard
{
    /**
     * Limit the number of records shown in the table
     *
     * @var integer
     */
    protected $limit = 20;

    /**
     * Created in the last 30 days
     *
     * @var integer
     */
    protected $days = 30;

    /**
     * Provide the table items
     *
     * @return \Illuminate\Http\Resources\Json\JsonResource
     */
    public function items() : iterable
    {
        return resolve(ContactRepository::class)
            ->pushCriteria(OwnContactsCriteria::class)
            ->columns(['id', 'first_name', 'last_name', 'created_at', 'email'])
            ->orderBy('created_at', 'desc')
            ->limit($this->limit)
            ->findWhere(
                [['created_at', '>', Carbon::asCurrentTimezone()->subDays($this->days)->inAppTimezone()]]
            )->map(function ($contact) {
                return [
                    'id'           => $contact->id,
                    'display_name' => $contact->display_name,
                    'email'        => $contact->email,
                    'created_at'   => $contact->created_at,
                    'path'         => $contact->path,
                ];
            });
    }

    /**
     * Provide the table fields
     *
     * @return array
     */
    public function fields() : array
    {
        return [
              ['key' => 'display_name', 'label' => __('contact.contact')],
              ['key' => 'email', 'label' => __('fields.contacts.email')],
              ['key' => 'created_at', 'label' => __('app.created_at')],
          ];
    }

    /**
     * Card title
     *
     * @return string
     */
    public function name() : string
    {
        return __('contact.cards.recently_created');
    }

    /**
    * jsonSerialize
    *
    * @return array
    */
    public function jsonSerialize() : array
    {
        return array_merge(parent::jsonSerialize(), [
            'help' => __('contact.cards.recently_created_info', ['total' => $this->limit, 'days' => $this->days]),
        ]);
    }
}
