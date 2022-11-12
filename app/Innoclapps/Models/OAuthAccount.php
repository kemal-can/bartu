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

namespace App\Innoclapps\Models;

use App\Innoclapps\Facades\Google;
use Database\Factories\OAuthAccountFactory;
use App\Innoclapps\OAuth\AccessTokenProvider;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OAuthAccount extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oauth_accounts';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
    * The attributes that should be cast to native types.
    *
    * @var array
    */
    protected $casts = [
        'requires_auth' => 'boolean',
        'access_token'  => 'encrypted',
        'user_id'       => 'int',
    ];

    /**
     * Boot the OAuthAccount Model
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        /**
         * Handle the deleted event
         */
        static::deleted(function ($account) {
            if ($account->type === 'google') {
                try {
                    Google::revokeToken($account->access_token);
                } catch (\Exception $e) {
                }
            }
        });
    }

    /**
     * Create new token provider
     *
     * @return \App\Innoclapps\OAuth\AccessTokenProvider
     */
    public function tokenProvider() : AccessTokenProvider
    {
        return new AccessTokenProvider($this->access_token, $this->email);
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return new OAuthAccountFactory;
    }
}
