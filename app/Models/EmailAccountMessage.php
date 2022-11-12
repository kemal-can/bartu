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

namespace App\Models;

use App\Innoclapps\Models\Model;
use App\Innoclapps\Media\HasMedia;
use App\Innoclapps\Concerns\HasAvatar;
use App\Support\EmailAccountMessageBody;
use App\Innoclapps\Contracts\Presentable;
use App\Innoclapps\Timeline\Timelineable;
use App\Innoclapps\Resources\Resourceable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class EmailAccountMessage extends Model implements Presentable
{
    use HasAvatar,
        HasMedia,
        Resourceable,
        Timelineable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email_account_id', 'remote_id', 'message_id',
        'subject', 'html_body', 'text_body', 'is_read',
        'is_draft', 'date', 'is_sent_via_app',
    ];

    /**
    * The attributes that should be cast to native types.
    *
    * Proper casts must be added to ensure the isDirty() method works fine
    * when checking whether the message is updated to broadcast to the front-end via sync
    *
    * @var array
    */
    protected $casts = [
        'date'             => 'datetime',
        'is_draft'         => 'boolean',
        'is_read'          => 'boolean',
        'is_sent_via_app'  => 'boolean',
        'email_account_id' => 'int',
    ];

    /**
     * A messages belongs to email account
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(\App\Models\EmailAccount::class, 'email_account_id');
    }

    /**
     * A message belongs to many folders
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function folders()
    {
        return $this->belongsToMany(
            \App\Models\EmailAccountFolder::class,
            'email_account_message_folders',
            'message_id',
            'folder_id'
        )
            ->using(\App\Models\EmailAccountMessageFolder::class);
    }

    /**
     * A message has many addresses
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function addresses()
    {
        return $this->hasMany(\App\Models\EmailAccountMessageAddress::class, 'message_id');
    }

    /**
     * A message can have many contacts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\morphedByMany
     */
    public function contacts()
    {
        return $this->morphedByMany(
            \App\Models\Contact::class,
            'messageable',
            'email_account_messageables',
            'message_id'
        );
    }

    /**
     * A message can have many companies.
     *
     * @return \Illuminate\Database\Eloquent\Relations\morphedByMany
     */
    public function companies()
    {
        return $this->morphedByMany(
            \App\Models\Company::class,
            'messageable',
            'email_account_messageables',
            'message_id'
        );
    }

    /**
     * A message can have many deals.
     *
     * @return \Illuminate\Database\Eloquent\Relations\morphedByMany
     */
    public function deals()
    {
        return $this->morphedByMany(
            \App\Models\Deal::class,
            'messageable',
            'email_account_messageables',
            'message_id'
        );
    }

    /**
     * A message from address
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function from()
    {
        return $this->hasOne(\App\Models\EmailAccountMessageAddress::class, 'message_id')
            ->where('address_type', 'from');
    }

    /**
     * A message sender address
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function sender()
    {
        return $this->hasOne(\App\Models\EmailAccountMessageAddress::class, 'message_id')
            ->where('address_type', 'sender');
    }

    /**
     * A message to address
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function to()
    {
        return $this->addresses()->where('address_type', 'to');
    }

    /**
     * A message cc address
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function cc()
    {
        return $this->addresses()->where('address_type', 'cc');
    }

    /**
     * A message bcc address
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function bcc()
    {
        return $this->addresses()->where('address_type', 'bcc');
    }

    /**
     * A message replyTo address
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function replyTo()
    {
        return $this->addresses()->where('address_type', 'replyTo');
    }

    /**
     * A message has many headers
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function headers()
    {
        return $this->hasMany(\App\Models\EmailAccountMessageHeader::class, 'message_id');
    }

    /**
     * Get the model display name
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function displayName() : Attribute
    {
        return Attribute::get(fn () => $this->subject);
    }

    /**
     * Get the URL path
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function path() : Attribute
    {
        return Attribute::get(function () {
            $accountId = $this->email_account_id;
            $folderId = $this->folders->first()->getKey();
            $messageId = $this->getKey();

            return "/inbox/$accountId/folder/$folderId/messages/$messageId";
        });
    }

    /**
     * Get the message attachments excluding the inline
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function attachments()
    {
        return static::media()->wherePivot('tag', '!=', 'embedded-attachments');
    }

    /**
     * Get the message inline attachments
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function inlineAttachments()
    {
        return static::media()->wherePivot('tag', '=', 'embedded-attachments');
    }

    /**
     * Determine if the message is a reply
     *
     * @return boolean
     */
    public function isReply() : bool
    {
        return ! is_null($this->headers->firstWhere('name', 'in-reply-to'))
            || ! is_null($this->headers->firstWhere('name', 'references'));
    }

    /**
     * Get the previewText attribute
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function previewText() : Attribute
    {
        return Attribute::get(fn () => $this->body()->previewText());
    }

    /**
     * Get the visibleText attribute without any quoted content
     *
     * NOTE: Sometimes the EmailParser may fail because it won't be able
     * to recognize the quoted text and will return empty message
     * In this case, just return the original preview text
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function visibleText() : Attribute
    {
        return Attribute::get(fn () => $this->body()->visibleText());
    }

    /**
     * Get the hiddenText attribute
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function hiddenText() : Attribute
    {
        return Attribute::get(fn () => $this->body()->hiddenText());
    }

    /**
     * Get the message body
     *
     * @return \App\Support\EmailAccountMessageBody
     */
    public function body()
    {
        return once(function () {
            return new EmailAccountMessageBody($this);
        });
    }

    /**
     * Get the relation name when the model is used as activity
     *
     * @return string
     */
    public function getTimelineRelation()
    {
        return 'emails';
    }

    /**
     * Get the activity front-end component
     *
     * @return string
     */
    public function getTimelineComponent()
    {
        return 'email';
    }
}
