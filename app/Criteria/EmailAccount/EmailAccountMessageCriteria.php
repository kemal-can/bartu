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

namespace App\Criteria\EmailAccount;

use App\Innoclapps\Contracts\Repository\CriteriaInterface;
use App\Innoclapps\Contracts\Repository\RepositoryInterface;

class EmailAccountMessageCriteria implements CriteriaInterface
{
    /**
     * Initialize EmailAccountMessageCriteria class
     *
     * @param int $accountId
     * @param int $folderId
     */
    public function __construct(protected $accountId, protected $folderId)
    {
    }

    /**
     * Apply criteria in query repository
     *
     * @param \Illumindata\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Builder $model
     * @param \App\Innoclapps\Contracts\Repository\RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository)
    {
        return $model->where('email_account_id', $this->accountId)
            ->whereHas('folders', function ($query) {
                return $query->where('folder_id', $this->folderId);
            });
    }
}
