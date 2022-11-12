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

namespace App\Innoclapps\Concerns;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

trait HasAvatar
{
    /**
     * Get Gravatar URL
     *
     * @param string|null $email
     * @param string|int $size
     *
     * @return string
     */
    public function getGravatarUrl(?string $email = null, string|int $size = '40') : string
    {
        $email ??= $this->email ?? '';

        return 'https://www.gravatar.com/avatar/' . md5(strtolower($email)) . '?s=' . $size;
    }

    /**
     * Get the model avatar URL.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function avatarUrl() : Attribute
    {
        return Attribute::get(function () {
            if (is_null($this->avatar)) {
                return $this->getGravatarUrl();
            }

            return $this->uploadedAvatarUrl;
        });
    }

    /**
     * Get the actual uploaded path URL for src image
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function uploadedAvatarUrl() : Attribute
    {
        return Attribute::get(function () {
            if (is_null($this->avatar)) {
                return null;
            }

            return Storage::url($this->avatar);
        });
    }
}
