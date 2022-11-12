<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use App\Models\EmailAccount;
use App\Models\EmailAccountFolder;
use App\Innoclapps\MailClient\FolderType;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmailAccountFolderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailAccountFolder::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'email_account_id' => EmailAccount::factory(),
            'remote_id'        => Str::uuid()->__toString(),
            'type'             => FolderType::INBOX,
            'name'             => 'INBOX',
            'display_name'     => 'INBOX',
            'syncable'         => true,
        ];
    }

    public function inbox()
    {
        return $this->state(function (array $attributes) {
            return [
                'type'         => FolderType::INBOX,
                'name'         => 'INBOX',
                'display_name' => 'INBOX',
            ];
        });
    }

    public function trash()
    {
        return $this->state(function (array $attributes) {
            return [
                'type'         => FolderType::TRASH,
                'name'         => 'TRASH',
                'display_name' => 'TRASH',
            ];
        });
    }

    public function sent()
    {
        return $this->state(function (array $attributes) {
            return [
                'type'         => FolderType::SENT,
                'name'         => 'SENT',
                'display_name' => 'SENT',
            ];
        });
    }
}
