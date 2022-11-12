<?php

namespace Tests\Unit\Innoclapps\MailClient;

use App\Innoclapps\MailClient\FolderIdentifier;
use Tests\TestCase;

class FolderIdentifierTest extends TestCase
{
    public function test_folder_identifier()
    {
        $identifier = new FolderIdentifier('id', 'INBOX');

        $this->assertSame('id', $identifier->key);
        $this->assertSame('INBOX', $identifier->value);
    }
}
