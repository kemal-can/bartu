<?php

namespace Tests\Unit\Innoclapps\Models;

use Tests\TestCase;
use Illuminate\Support\Facades\Crypt;
use App\Innoclapps\Models\OAuthAccount;
use App\Innoclapps\OAuth\AccessTokenProvider;

class OAuthAccountTest extends TestCase
{
    public function test_it_encrypts_the_oauth_account_access_token()
    {
        Crypt::shouldReceive('encrypt')->once()
            ->with('token', false)
            ->andReturnArg(0);

        new OAuthAccount(['access_token' => 'token']);
    }

    public function test_it_decrypts_the_oauth_account_access_token()
    {
        $account = new OAuthAccount(['access_token' => 'token']);

        Crypt::shouldReceive('decrypt')->once()
            ->andReturn('token');

        $this->assertEquals('token', $account->access_token);
    }

    public function test_oauth_account_has_access_token_provider()
    {
        $account = new OAuthAccount(['access_token' => 'token', 'email' => 'john@example.com']);

        $this->assertInstanceOf(AccessTokenProvider::class, $account->tokenProvider());
    }
}
