<?php

namespace Tests\Unit\Repository;

use Tests\TestCase;
use App\Enums\SyncState;
use App\Models\EmailAccount;
use App\Models\EmailAccountFolder;
use App\Innoclapps\Models\OAuthAccount;
use Illuminate\Support\Facades\Request;
use App\Contracts\Repositories\EmailAccountRepository;

class EmailAccountRepositoryTest extends TestCase
{
    protected $repository;

    protected function setUp() : void
    {
        parent::setUp();
        $this->repository = app(EmailAccountRepository::class);
    }

    protected function tearDown() : void
    {
        unset($this->repository);
        parent::tearDown();
    }

    public function test_account_is_marked_as_personal_when_user_id_is_provided()
    {
        $user    = $this->signIn();
        $payload = EmailAccount::factory()->personal($user)->raw();

        $account = $this->repository->create($payload);

        $this->assertTrue($account->isPersonal());
        $this->assertFalse($account->isShared());
    }

    public function test_account_is_marked_as_shared_when_user_id_is_not_provided()
    {
        $this->signIn();
        $payload = EmailAccount::factory()->shared()->raw();

        $account = $this->repository->create($payload);

        $this->assertTrue($account->isShared());
        $this->assertFalse($account->isPersonal());
    }

    public function test_from_name_header_is_not_set_for_personal_email_accounts()
    {
        $user    = $this->signIn();
        $payload = EmailAccount::factory()->personal($user)->raw();

        $account = $this->repository->create(array_merge($payload, [
            'from_name_header' => '{agent} from {company}',
        ]));

        $this->assertNull($account->getMeta('from_name_header'));
    }

    public function test_from_name_header_is_set_for_shared_email_accounts()
    {
        $this->signIn();
        $payload = EmailAccount::factory()->shared()->raw();

        $account = $this->repository->create(array_merge($payload, [
            'from_name_header' => 'custom from name header',
        ]));

        $this->assertSame('custom from name header', $account->from_name_header);

        $account = $this->repository->update(array_merge($payload, [
            'from_name_header' => 'changed custom from name header',
        ]), $account->id);

        $this->assertSame('changed custom from name header', $account->from_name_header);
    }

    public function test_when_from_name_header_is_empty_it_uses_the_default()
    {
        $this->signIn();
        $payload = EmailAccount::factory()->shared()->raw();

        $account = $this->repository->create(array_merge($payload, [
            'from_name_header' => '',
        ]));

        $this->assertSame(EmailAccount::DEFAULT_FROM_NAME_HEADER, $account->from_name_header);
    }

    public function test_when_from_name_header_is_null_it_uses_the_default()
    {
        $this->signIn();
        $payload = EmailAccount::factory()->shared()->raw();

        $account = $this->repository->create(array_merge($payload, [
            'from_name_header' => null,
        ]));

        $this->assertSame(EmailAccount::DEFAULT_FROM_NAME_HEADER, $account->from_name_header);
    }

    public function test_email_account_folders_are_saved()
    {
        $this->signIn();
        $payload = EmailAccount::factory()->raw();

        $account = $this->repository->create($payload = array_merge($payload, [
            'folders' => [
                EmailAccountFolder::factory()->raw(['name' => 'INBOX', 'syncable' => true]),
            ],
        ]));

        $this->assertCount(1, $account->folders);

        $payload['folders'] = [
            EmailAccountFolder::factory()->raw(['name' => 'INBOX', 'syncable' => false]),
            EmailAccountFolder::factory()->raw(['name' => 'New Folder']),
        ];

        $account = $this->repository->update($payload, $account->id);

        $this->assertCount(2, $account->folders);
        $this->assertFalse($account->folders->firstWhere('name', 'INBOX')->syncable);
    }

    public function test_duplicate_folders_are_not_saved()
    {
        $this->signIn();
        $payload = EmailAccount::factory()->raw();

        $account = $this->repository->create(array_merge($payload, [
            'folders' => [
                EmailAccountFolder::factory()->raw(['name' => 'INBOX']),
                EmailAccountFolder::factory()->raw(['name' => 'INBOX']),
            ],
        ]));

        $this->assertCount(1, $account->folders);

        $payload['folders'] = [
            EmailAccountFolder::factory()->raw(['name' => 'INBOX']),
            EmailAccountFolder::factory()->raw(['name' => 'INBOX']),
        ];

        $account = $this->repository->update($payload, $account->id);
        $this->assertCount(1, $account->folders);
    }

    public function test_trash_and_sent_folder_are_set_on_create()
    {
        $this->signIn();
        $payload = EmailAccount::factory()->raw();

        $account = $this->repository->create(array_merge($payload, [
            'folders' => [
                $sent = EmailAccountFolder::factory()->sent()->raw(),
                $trash = EmailAccountFolder::factory()->trash()->raw(),
            ],
        ]));

        $this->assertNotNull($account->trashFolder);
        $this->assertNotNull($account->sentFolder);
        $this->assertSame($trash['name'], $account->trashFolder->name);
        $this->assertSame($sent['name'], $account->sentFolder->name);
    }

    public function test_email_account_folder_can_be_marked_as_not_syncable()
    {
        $this->signIn();
        $payload = EmailAccount::factory()->raw();

        $account = $this->repository->create(array_merge($payload, [
            'folders' => [
                $folder1 = EmailAccountFolder::factory()->raw(),
                $folder2 = EmailAccountFolder::factory()->raw(['syncable' => false, 'name' => 'SENT']),
            ],
        ]));

        $this->assertCount(1, $account->folders->active());

        $payload['folders'] = [
            [...$folder1, ...['syncable' => false]],
            $folder2,
        ];

        $account = $this->repository->update($payload, $account->id);
        $this->assertCount(0, $account->folders->active());
    }

    public function test_folder_child_folders_are_saved()
    {
        $this->signIn();
        $payload            = EmailAccount::factory()->raw();
        $parent             = EmailAccountFolder::factory()->raw();
        $child              = EmailAccountFolder::factory()->sent()->raw(['name' => 'INBOX 1']);
        $child['children']  = [$deepChild = EmailAccountFolder::factory()->sent()->raw(['name' => 'INBOX 2'])];
        $parent['children'] = [$child];

        $account = $this->repository->create(array_merge($payload, [
            'folders' => [$parent],
        ]));

        $tree = $account->folders->createTreeFromActive(Request::instance());

        $this->assertCount(3, $account->folders);
        $this->assertCount(1, $tree);
        $this->assertSame($parent['name'], $tree[0]['name']);
        $this->assertSame($child['name'], $tree[0]['children'][0]['name']);
        $this->assertSame($deepChild['name'], $tree[0]['children'][0]['children'][0]['name']);
    }

    public function test_it_can_find_email_account_by_email()
    {
        $account = EmailAccount::factory()->create();

        $this->assertNotNull($this->repository->findByEmail($account->email));
        $this->assertNull($this->repository->findByEmail('dummy-email'));
    }

    public function test_it_can_mark_account_as_primary()
    {
        $user    = $this->signIn();
        $account = EmailAccount::factory()->create();

        $this->repository->markAsPrimary($account, $user);

        $this->assertTrue($account->isPrimary());
    }

    public function test_it_can_unmark_account_as_primary()
    {
        $user    = $this->signIn();
        $account = EmailAccount::factory()->create();
        $this->repository->markAsPrimary($account, $user);

        $this->repository->removePrimary($user);

        $this->assertFalse($account->isPrimary());
    }

    public function test_it_can_set_that_account_requires_authentication()
    {
        $account = EmailAccount::factory()->create();

        $this->repository->setRequiresAuthentication($account->id);

        $this->assertTrue($account->fresh()->requires_auth);
    }

    public function test_it_sets_requires_authentication_on_the_auth_account()
    {
        $account = EmailAccount::factory()->create([
            'access_token_id' => OAuthAccount::factory()->create()->id,
        ]);

        $this->repository->setRequiresAuthentication($account->id);

        $this->assertTrue($account->fresh()->requires_auth);
        $this->assertTrue($account->oAuthAccount->requires_auth);
    }

    public function test_it_can_set_that_account_doesnt_requires_authentication()
    {
        $account = EmailAccount::factory()->requiresAuth()->create();

        $this->repository->setRequiresAuthentication($account->id, false);

        $this->assertFalse($account->fresh()->requires_auth);
    }

    public function test_it_sents_doesnt_requires_authentication_on_the_oauth_account()
    {
        $account = EmailAccount::factory()->create([
            'access_token_id' => OAuthAccount::factory()->requiresAuth()->create()->id,
        ]);

        $this->repository->setRequiresAuthentication($account->id, false);

        $this->assertFalse($account->fresh()->requires_auth);
        $this->assertFalse($account->oAuthAccount->requires_auth);
    }

    public function test_personal_email_accounts_can_be_retrieved()
    {
        $user     = $this->createUser();
        $personal = EmailAccount::factory()->personal($user)->create();
        EmailAccount::factory()->shared()->create();

        $accounts = $this->repository->getPersonal($user->id);

        $this->assertCount(1, $accounts);
        $this->assertSame($personal->email, $accounts[0]->email);
    }

    public function test_shared_email_accounts_can_be_retrieved()
    {
        EmailAccount::factory()->personal()->create();
        $shared = EmailAccount::factory()->shared()->create();

        $accounts = $this->repository->getShared();

        $this->assertCount(1, $accounts);
        $this->assertSame($shared->email, $accounts[0]->email);
    }

    public function test_syncable_email_accounts_can_be_retrieved()
    {
        EmailAccount::factory()->syncDisabled()->create();
        EmailAccount::factory()->syncStopped()->create();
        EmailAccount::factory()->create();
        EmailAccount::factory()->create(['sync_state' => SyncState::ENABLED]);

        $this->assertCount(2, $this->repository->getSyncable());
    }

    public function test_it_can_enable_sync_for_account()
    {
        $account = EmailAccount::factory()->syncDisabled()->create();

        $this->repository->enableSync($account->id);

        $this->assertFalse($account->fresh()->isSyncDisabled());
    }

    public function test_it_can_set_sync_state_for_account()
    {
        $account = EmailAccount::factory()->create();

        $this->repository->setSyncState($account->id, SyncState::DISABLED, 'Disabled via tests.');
        $account->refresh();

        $this->assertSame('Disabled via tests.', $account->sync_state_comment);
        $this->assertSame(SyncState::DISABLED, $account->sync_state);
    }
}
