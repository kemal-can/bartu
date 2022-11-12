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

namespace App\Innoclapps\MailableTemplates;

use Mustache_Engine;
use Illuminate\Support\Str;
use App\Innoclapps\MailableTemplates\Placeholders\Collection;
use App\Innoclapps\MailableTemplates\Exceptions\CannotRenderMailableTemplate;

class Renderer
{
    /**
     * Initialize new Renderer instance.
     *
     * @param string $htmlTemplate
     * @param string $subject
     * @param \App\Innoclapps\MailableTemplates\Placeholders\Collection|null $placeholders
     * @param string|null $htmlLayout
     * @param string|null $textTemplate
     * @param string|null $textLayout
     * @param \Mustache_Engine $mustache
     */
    public function __construct(
        protected string $htmlTemplate,
        protected string $subject,
        protected ?Collection $placeholders = null,
        protected ?string $htmlLayout = null,
        protected ?string $textTemplate = null,
        protected ?string $textLayout = null,
        protected Mustache_Engine $mustache,
    ) {
    }

    /**
     * Render mail template HTML layout
     *
     * @return string|null
     */
    public function renderHtmlLayout()
    {
        $body = $this->mustache->render(
            $this->htmlTemplate,
            $placeholders = $this->placeholders?->parse(),
        );

        $layout = $this->htmlLayout;

        if (view()->exists($layout)) {
            $layout = view($layout, array_merge($placeholders ?: [], [
                'subject' => $this->renderSubject(),
            ]))->render();
        }

        return $this->renderInLayout($body, $layout);
    }

    /**
     * Render mail template text layout
     *
     * @return string|null
     */
    public function renderTextLayout()
    {
        if (! $this->textTemplate) {
            return null;
        }

        $body = $this->mustache->render(
            $this->textTemplate,
            $this->placeholders?->parse('text')
        );

        return $this->renderInLayout($body, $this->textLayout);
    }

    /**
     * Render mail template subject
     *
     * @return string
     */
    public function renderSubject()
    {
        return $this->mustache->render(
            $this->subject,
            $this->placeholders?->parse('text')
        );
    }

    /**
     * Render mail template content in layout
     *
     * @throws \App\Innoclapps\MailableTemplates\Exceptions\CannotRenderMailableTemplate
     *
     * @param string $body
     * @param string|null $layout
     *
     * @return string
     */
    protected function renderInLayout(string $body, ?string $layout)
    {
        $this->guardAgainstInvalidLayout($layout ??= '{{{ mailBody }}}');

        $data = array_merge(['mailBody' => $body], $this->placeholders?->parse());

        return $this->mustache->render($layout, $data);
    }

    /**
     * Guard layout body
     *
     * @throws \App\Innoclapps\MailableTemplates\Exceptions\CannotRenderMailableTemplate
     *
     * Ensures that body placeholder exists in the layout
     *
     * @param string $layout
     *
     * @return void
     */
    protected function guardAgainstInvalidLayout(string $layout)
    {
        $bodyAble = [
            '{{{mailBody}}}',
            '{{{ mailBody }}}',
            '{{mailBody}}',
            '{{ mailBody }}',
            '{{ $mailBody }}',
            '{!! $mailBody !!}',
        ];

        if (! Str::contains($layout, $bodyAble)) {
            throw CannotRenderMailableTemplate::layoutDoesNotContainABodyPlaceHolder();
        }
    }
}
