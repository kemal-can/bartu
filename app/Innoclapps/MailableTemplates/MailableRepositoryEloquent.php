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

use App\Innoclapps\Repository\AppRepository;
use App\Innoclapps\Facades\MailableTemplates;
use App\Innoclapps\Models\MailableTemplate as Model;
use App\Innoclapps\Contracts\Repositories\MailableRepository;

class MailableRepositoryEloquent extends AppRepository implements MailableRepository
{
    /**
     * Specify Model class name
     *
     * @return string
     */
    public static function model()
    {
        return Model::class;
    }

    /**
     * Retrieve mail templates in specific locale
     *
     * @param string $locale
     *
     * @return mixed
     */
    public function forLocale(string $locale)
    {
        MailableTemplates::seedIfRequired();

        return $this->findWhere(['locale' => $locale]);
    }

    /**
     * Get mail template for/via mailable
     *
     * @param \App\Innoclapps\MailableTemplates\MailableTemplate|string $mailable
     * @param string $locale
     *
     * @return \App\Innoclapps\Models\MailableTemplate
     */
    public function forMailable(MailableTemplate|string $mailable, string $locale) : Model
    {
        MailableTemplates::seedIfRequired();

        $className = $mailable instanceof MailableTemplate ? $mailable::class : $mailable;

        return $this->findWhere(['mailable' => $className, 'locale' => $locale])->first();
    }
}
