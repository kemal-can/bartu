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

namespace App\Workflows\Actions;

use Illuminate\Support\Str;
use App\Innoclapps\Fields\Text;
use App\Innoclapps\Workflow\Action;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Innoclapps\Resources\Http\ResourceRequest;

class WebhookAction extends Action implements ShouldQueue
{
    /**
     * Action name
     *
     * @return string
     */
    public static function name() : string
    {
        return __('workflow.actions.webhook');
    }

    /**
     * Run the trigger
     *
     * @return array
     */
    public function run()
    {
        Http::withoutVerifying()
            ->withHeaders($this->getHeaders())
            ->post('https://' . $this->url, $payload = $this->getPayload())
            ->onError(function ($error) {
                Log::debug('Webhook action failed with status:' . $error->status() . ', reason:' . $error->reason());
            });

        return $payload;
    }

    /**
     * Get the webhook request headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return array_merge(with([], function ($headers) {
            if ($this->header_name) {
                $headers[$this->header_name] = $this->header_value;
            }

            return $headers;
        }), [
            'User-Agent' => 'bartu CRM',
        ]);
    }

    /**
     * Action available fields
     *
     * @return array
     */
    public function fields() : array
    {
        return [
            Text::make('url')->inputType('url')
                ->inputGroupPrepend('https://')
                ->withMeta(['attributes' => ['class' => '!pl-16']])
                ->help(__('workflow.actions.webhook_url_info'))
                ->helpDisplay('text')
                ->rules(['required', function ($attribute, $value, $fail) {
                    if (Str::startsWith($value, ['https://', 'http://'])) {
                        $fail(__('workflow.validation.invalid_webhook_url'));
                    }
                }]),

            Text::make('header_name')->withMeta([
                'attributes' => [
                        'placeholder' => __('workflow.fields.with_header_name'),
                    ],
                ]),

            Text::make('header_value')->withMeta([
                'attributes' => [
                        'placeholder' => __('workflow.fields.with_header_value'),
                    ],
                ]),
        ];
    }

    /**
     * Set the trigger data
     *
     * @param array $data
     *
     * @return static
     */
    public function setData($data) : static
    {
        parent::setData($data);

        // We will set the payload while setting up the data for the action
        // this helps properly serialize the action when in the queue and keep
        // as the $data property is serialized for the action, after the action
        // is unserialized, we will still have the data from the previous request
        if ($this->viaModelTrigger()) {
            $this->setPayload(json_decode(
                json_encode(
                    $this->resource->createJsonResource(
                        $this->resource->displayQuery()->find($this->model->getKey()),
                        true,
                        // Not needed but for consistency, to use the ResourceRequest class
                        app(ResourceRequest::class)->setResource($this->resource->name())
                    )
                ),
                true
            ));
        }

        return $this;
    }

    /**
     * Get the request payload data
     *
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * Set the request payload data
     *
     * @param array $data
     */
    public function setPayload($data)
    {
        $this->payload = (array) $data;

        return $this;
    }
}
