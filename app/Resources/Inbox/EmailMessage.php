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

namespace App\Resources\Inbox;

use Illuminate\Http\Request;
use App\Innoclapps\Table\Table;
use App\Innoclapps\Resources\Resource;
use App\Innoclapps\MailClient\FolderType;
use App\Innoclapps\Contracts\Resources\Tableable;
use App\Innoclapps\Resources\Http\ResourceRequest;
use App\Http\Resources\EmailAccountMessageResource;
use App\Criteria\EmailAccount\EmailAccountMessageCriteria;
use App\Criteria\EmailAccount\EmailAccountsForUserCriteria;
use App\Contracts\Repositories\EmailAccountMessageRepository;

class EmailMessage extends Resource implements Tableable
{
    /**
     * Indicates whether the resource is globally searchable
     *
     * @var boolean
     */
    public static bool $globallySearchable = true;

    /**
     * Get the underlying resource repository
     *
     * @return \App\Innoclapps\Repository\AppRepository
     */
    public static function repository()
    {
        return resolve(EmailAccountMessageRepository::class)->with(['folders', 'account'])
            ->scopeQuery(function ($query) {
                return $query->whereHas('account', function ($query) {
                    return EmailAccountsForUserCriteria::applyQuery($query);
                });
            });
    }

    /**
     * Get the json resource that should be used for json response
     *
     * @return string
     */
    public function jsonResource() : string
    {
        return EmailAccountMessageResource::class;
    }

    /**
     * The resource name
     *
     * @return string
     */
    public static function name() : string
    {
        return 'emails';
    }

    /**
     * Get the resource relationship name when it's associated
     *
     * @return string
     */
    public function associateableName() : string
    {
        return 'emails';
    }

    /**
     * Create query when the resource is associated for index
     *
     * @param \App\Innoclapps\Models\Model $primary
     * @param bool $applyOrder
     *
     * @return \App\Innoclapps\Repositories\AppRepository
     */
    public function associatedIndexQuery($primary, $applyOrder = true)
    {
        return tap(parent::associatedIndexQuery($primary, $applyOrder), function ($repository) {
            $repository->withResponseRelations()
                ->whereHas('folders.account', function ($query) {
                    return $query->whereColumn('folder_id', '!=', 'trash_folder_id');
                });
        });
    }

    /**
     * Provide the resource table class
     *
     * @param \App\Innoclapps\Repository\BaseRepository $repository
     * @param \Illuminate\Http\Request $request
     *
     * @return \App\Innoclapps\Table\Table
     */
    public function table($repository, Request $request) : Table
    {
        $criteria = new EmailAccountMessageCriteria(
            $request->input('account_id'),
            $request->input('folder_id')
        );

        $tableClass = $this->getTableClassByFolderType($request->folder_type);

        return new $tableClass($repository->pushCriteria($criteria), $request);
    }

    /**
     * Provides the resource available actions
     *
     * @param \App\Innoclapps\Resources\Http\ResourceRequest $request
     *
     * @return array
     */
    public function actions(ResourceRequest $request) : array
    {
        return [
            (new Actions\EmailAccountMessageMarkAsRead)->withoutConfirmation(),
            (new Actions\EmailAccountMessageMarkAsUnread)->withoutConfirmation(),
            new Actions\EmailAccountMessageMove,
            new Actions\EmailAccountMessageDelete,
        ];
    }

    /**
     * Get the table FQCN by given folder type
     *
     * @param string $type
     *
     * @return string
     */
    protected function getTableClassByFolderType($type)
    {
        if ($type === FolderType::OTHER || $type == 'incoming') {
            return IncomingMessageTable::class;
        }

        return OutgoingMessageTable::class;
    }
}
