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

namespace App\Innoclapps\Calendar\Outlook;

use App\Innoclapps\Facades\Microsoft as Api;
use App\Innoclapps\Calendar\AbstractCalendar;
use Beta\Microsoft\Graph\Model\Event as EventModel;
use App\Innoclapps\OAuth\Exceptions\ConnectionErrorException;
use App\Innoclapps\Contracts\Calendar\Calendar as CalendarInterface;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class Calendar extends AbstractCalendar implements CalendarInterface
{
    /**
     * Get the calendar ID
     *
     * @return string
     */
    public function getId() : string
    {
        return $this->getEntity()->getId();
    }

    /**
     * Get the calendar title
     *
     * @return string
     */
    public function getTitle() : string
    {
        return $this->getEntity()->getName();
    }

    /**
     * Get events that are starting only from the given
     *
     * @param string $dateTime
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws ConnectionErrorException
     */
    public function getEventsFrom($dateTime)
    {
        return $this->getDeltaEvents(null, $dateTime);
    }

    /**
     * https://docs.microsoft.com/en-us/graph/delta-query-messages
     *
     * @param null|string $deltaToken
     * @param null|string $startFrom Get messages starting from specific date and time
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws ConnectionErrorException
     */
    public function getDeltaEvents($deltaLink = null, $startFrom = null)
    {
        $deltaEndpoint = '/me/calendars/' . $this->getId() . '/events/delta';

        if (! $deltaLink && $startFrom) {
            $startFromFormatted = (new \DateTime($startFrom))->format(\DateTimeInterface::ISO8601);

            $deltaEndpoint .= '?' . http_build_query([
                'startDateTime' => $startFromFormatted,
            ]);
        }

        $endpoint = $deltaLink ?? $deltaEndpoint;

        try {
            $originalVersion = tap(Api::getApiVersion(), function () {
                Api::setApiVersion('beta');
            });

            $deltaIterator = Api::createCollectionGetRequest($endpoint)->setReturnType(EventModel::class);

            $events = collect(Api::iterateCollectionRequest($deltaIterator));
        } catch (IdentityProviderException $e) {
            throw new ConnectionErrorException($e->getMessage(), $e->getCode(), $e);
        } finally {
            Api::setApiVersion($originalVersion);
        }

        $events->deltaLink = $deltaIterator->getDeltaLink();

        return $events;
    }
}
