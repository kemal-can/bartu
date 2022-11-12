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

use App\Enums\WebFormSection;
use Illuminate\Http\Resources\Json\JsonResource;

class WebFormResource extends JsonResource
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
        $cleanSubmitData = ['success_message'];

        $cleanSectionsData = [
            WebFormSection::INTRODUCTION->value => ['message'],
            WebFormSection::MESSAGE->value      => ['message'],
            WebFormSection::FIELD->value        => function (&$section) {
                $section['field']['label'] = clean($section['label']);
            },
            WebFormSection::FILE->value => function (&$section) {
                $section['field']['label'] = clean($section['label']);
            },
        ];

        return [
            'id'       => $this->id,
            'title'    => $this->title,
            'status'   => $this->status,
            'uuid'     => $this->uuid,
            'sections' => collect($this->sections)
                ->map(function ($section) use ($cleanSectionsData) {
                    if (array_key_exists($section['type'], $cleanSectionsData)) {
                        if (is_callable($cleanSectionsData[$section['type']])) {
                            $cleanSectionsData[$section['type']]($section);
                        } else {
                            foreach ($cleanSectionsData[$section['type']] as $key) {
                                $section = array_merge($section, [$key => clean($section[$key])]);
                            }
                        }
                    }

                    return $section;
                }),
            'submit_data' => collect($this->submit_data)
                ->mapWithKeys(function ($value, $key) use ($cleanSubmitData) {
                    if (in_array($key, $cleanSubmitData)) {
                        $value = clean($value);
                    }

                    return [$key => $value];
                }),
            'styles'            => $this->styles,
            'notifications'     => $this->notifications,
            'title_prefix'      => $this->title_prefix,
            'locale'            => $this->locale,
            'user_id'           => $this->user_id,
            'user'              => new UserResource($this->whenLoaded('user')),
            'public_url'        => $this->publicUrl,
            'total_submissions' => $this->total_submissions,
        ];
    }
}
