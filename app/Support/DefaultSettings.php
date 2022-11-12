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

namespace App\Support;

class DefaultSettings
{
    /**
     * An array of available options
     *
     * @var array
     */
    private array $options = [
        'company_name'                          => null,
        'company_country_id'                    => null,
        'tax_label'                             => 'TAX',
        'tax_rate'                              => 0,
        'tax_type'                              => 'no_tax',
        'discount_type'                         => 'percent',
        'recaptcha_site_key'                    => null,
        'recaptcha_secret_key'                  => null,
        'recaptcha_ignored_ips'                 => null,
        'date_format'                           => 'F j, Y',
        'time_format'                           => 'H:i',
        'currency'                              => 'USD',
        'logo_light'                            => null,
        'logo_dark'                             => null,
        'last_cron_run'                         => null,
        'first_day_of_week'                     => 0, // Sunday
        'auto_associate_company_to_contact'     => 1,
        'msgraph_client_id'                     => null,
        'msgraph_client_secret'                 => null,
        'msgraph_client_secret_configured_at'   => null,
        'google_client_id'                      => null,
        'google_client_secret'                  => null,
        'pusher_app_id'                         => null,
        'pusher_app_key'                        => null,
        'pusher_app_secret'                     => null,
        'pusher_app_cluster'                    => null,
        'system_email_account_id'               => null,
        'purchase_key'                          => null,
        'privacy_policy'                        => null,
        'twilio_app_sid'                        => null,
        'twilio_auth_token'                     => null,
        'twilio_account_sid'                    => null,
        'twilio_number'                         => null,
        'allowed_extensions'                    => 'jpg, jpeg, png, gif, svg, pdf, aac, ogg, oga, mp3, wav, mp4, m4v, mov, ogv, webm, zip, rar, doc, docx, txt, text, xml, json, xls, xlsx, odt, csv, ppt, pptx, ppsx, ics, eml',
        '_app_url'                              => null,
        '_server_ip'                            => null,
        'send_contact_attends_to_activity_mail' => false,
        'require_calling_prefix_on_phones'      => true,
        'default_activity_type'                 => null,
        'contact_fields_height'                 => null,
        'company_fields_height'                 => null,
        'deal_fields_height'                    => null,
        'disable_password_forgot'               => false,
        'block_bad_visitors'                    => false,
        'allow_lost_reason_enter'               => true,
    ];

    /**
     * Get default option(s)
     *
     * @param string $option
     *
     * @return array|string|null
     */
    public function get($option = null)
    {
        if ($option) {
            return $this->options[$option] ?? null;
        }

        return $this->options;
    }

    /**
     * Get the settings which are required
     *
     * @return array
     */
    public function getRequired() : array
    {
        return [
             'currency',
             'date_format',
             'time_format',
             'first_day_of_week',
             'allowed_extensions',
             'tax_label',
             'tax_type',
             'discount_type',
             'default_activity_type',
         ];
    }

    /**
     * Check whether the given settings key is required
     *
     * @return boolean
     */
    public function isRequired(string $key) : bool
    {
        return in_array($key, $this->getRequired());
    }
}
