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

use Spatie\Url\Url;
use App\Models\User;
use KubAT\PhpSimple\HtmlDomParser;
use App\Notifications\UserMentioned;
use Illuminate\Support\Facades\Auth;
use App\Contracts\Repositories\UserRepository;

class PendingMention
{
    /**
     * Mentionable url
     *
     * @var string
     */
    protected string $url;

    /**
     * @var array
     */
    protected array $urlQueryParameters = [];

    /**
     * Mentioned users
     *
     * @var array
     */
    protected array $users = [];

    /**
     * Initialize new PendingMention instance.
     *
     * @param string $text
     */
    public function __construct(protected string $text)
    {
        $this->users = $this->findMentionedUsers();
    }

    /**
     * Set the URL for the mentionable
     *
     * @param string $url
     */
    public function setUrl(string $url) : static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Notify the mentioned users
     *
     * @param \App\Models\User|null $mentioner
     *
     * @return void
     */
    public function notify(User $mentioner = null) : void
    {
        collect($this->users)->each(function ($user) use ($mentioner) {
            $user->notify(
                new UserMentioned((string) $this->getMentionUrl(), $mentioner ?? Auth::user())
            );
        });
    }

    /**
     * Add query parameter to the mention url
     *
     * @param array|string $key
     * @param string|null $value
     *
     * @return static
     */
    public function withUrlQueryParameter(array|string $key, ?string $value = null) : static
    {
        if (is_array($key)) {
            foreach ($key as $parameter => $value) {
                $this->withUrlQueryParameter($parameter, $value);
            }
        } elseif (! is_null($value)) {
            $this->urlQueryParameters[$key] = $value;
        }

        return $this;
    }

    /**
     * Get the mention URL
     *
     * @return \Spatie\Url\Url
     */
    public function getMentionUrl() : Url
    {
        $url = $this->createUrlInstance();

        foreach ($this->urlQueryParameters as $key => $value) {
            $url = $url->withQueryParameter($key, $value);
        }

        return $url;
    }

    /**
     * Create new URL instance
     *
     * @return \Spatie\Url\Url
     */
    protected function createUrlInstance() : Url
    {
        return Url::fromString($this->url);
    }

    /**
     * Find the mentioned users from the text
     *
     * @return array
     */
    protected function findMentionedUsers() : array
    {
        if ($this->text === '') {
            return [];
        }

        $mentioneduserIds = [];
        $dom              = HtmlDomParser::str_get_html($this->text);

        foreach ($dom->find('[data-mention-id]') as $element) {
            if ($element->getAttribute('data-notified') == 'false') {
                $mentioneduserIds[] = $element->getAttribute('data-mention-id');
            }
        }

        return app(UserRepository::class)->findMany(
            array_map('intval', array_unique($mentioneduserIds))
        )->all();
    }

    /**
     * check whether there are mentioned users
     *
     * @return boolean
     */
    public function hasMentions() : bool
    {
        return count($this->users) > 0;
    }

    /**
     * Get the updated text with content attribute data-notified to true so the next time these users won't be notified.
     *
     * @return string
     */
    public function getUpdatedText() : string
    {
        if (! $this->hasMentions()) {
            return $this->text;
        }

        $dom = HtmlDomParser::str_get_html($this->text);

        foreach ($dom->find('[data-mention-id]') as $element) {
            $element->setAttribute('data-notified', 'true');
        }

        return $dom->save();
    }
}
