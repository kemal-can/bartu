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

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CalendarEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'         => $this->getKey(),
            'title'      => $this->getCalendarTitle($request->viewName),
            'start'      => $this->getCalendarStartDate($request->viewName),
            'end'        => $this->getCalendarEndDate($request->viewName),
            'allDay'     => $this->isAllDay(),
            'isReadOnly' => $request->user()->cant('update', $this->resource),
            'textColor'  => method_exists($this->resource, 'getCalendarEventTextColor') ?
                $this->getCalendarEventTextColor() :
                null,
            'backgroundColor' => $bgColor = method_exists($this->resource, 'getCalendarEventBgColor') ?
                $this->getCalendarEventBgColor() :
                '',
            'borderColor' => method_exists($this->resource, 'getCalendarEventBorderColor') ?
                $this->getCalendarEventBorderColor() :
                $bgColor,
            'extendedProps' => [
                'event_type' => strtolower(class_basename($this->resource)),
            ],
        ];
    }
}
