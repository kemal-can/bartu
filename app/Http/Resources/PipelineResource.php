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

use App\Innoclapps\Resources\Http\JsonResource;

class PipelineResource extends JsonResource
{
    use ProvidesCommonData;

    /**
     * Transform the resource collection into an array.
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return array
     */
    public function toArray($request)
    {
        return $this->withCommonData([
            'name' => $this->name,
            $this->mergeWhen($this->relationLoaded('userOrder'), function () {
                return [
                    'user_display_order' => $this->userOrder?->display_order,
                ];
            }),
            $this->mergeWhen(! $request->isZapier(), [
                'visibility_group' => $this->whenLoaded('visibilityGroup', function () {
                    return [
                        'type'       => $this->visibilityGroup->type,
                        'depends_on' => $this->getVisibilityDependentsIds(),
                    ];
                }),
                'flag'                   => $this->flag,
                'user_default_sort_data' => $this->default_sort_data,
                'stages'                 => StageResource::collection($this->whenLoaded('stages')),
            ]),
        ], $request);
    }

    /**
     * Get the dependents ids
     *
     * @return array
     */
    protected function getVisibilityDependentsIds() : array
    {
        return collect([])->when(
            $this->shouldIncludeVisibilityGroup('teams'),
            fn ($collection) => $collection->concat($this->visibilityGroup->teams->modelKeys())
        )->when(
            $this->shouldIncludeVisibilityGroup('users'),
            fn ($collection) => $collection->concat($this->visibilityGroup->users->modelKeys())
        )->all();
    }

    /**
     * Check whether a visibility group should be included in the respons
     *
     * @param string $name
     *
     * @return boolean
     */
    protected function shouldIncludeVisibilityGroup(string $name) : bool
    {
        return $this->visibilityGroup->type === $name && $this->visibilityGroup->relationLoaded($name);
    }
}
