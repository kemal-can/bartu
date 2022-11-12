<?php

namespace Tests\Feature\Controller\Api\Resource;

use Tests\TestCase;
use App\Models\Contact;
use Database\Seeders\PermissionsSeeder;
use Tests\Concerns\TestsImportAndExport;
use Illuminate\Database\Eloquent\Factories\Sequence;

class ExportControllerTest extends TestCase
{
    use TestsImportAndExport;

    public function test_unauthenticated_user_cannot_access_export_endpoints()
    {
        $this->postJson('/api/contacts/export')->assertUnauthorized();
    }

    public function test_non_exportable_resource_cannot_be_exported()
    {
        $this->signIn();

        $this->postJson('/api/users/export')->assertNotFound();
    }

    public function test_user_can_perform_export_on_resource()
    {
        $this->signIn();

        Contact::factory()->count(2)->create();

        try {
            $response = $this->postJson('/api/contacts/export', [
                'type'   => 'csv',
                'period' => 'last_7_days',
            ])->assertOk()
                ->assertHeader('Content-Disposition', 'attachment; filename=contacts.csv')
                ->assertDownload();

            $csvArray = $this->csvToArray($response->getFile()->getPathname());

            $this->assertCount(2, $csvArray);
        } finally {
            if (is_file($response->getFile()->getPathname())) {
                unlink($response->getFile()->getPathname());
            }
        }
    }

    public function test_own_criteria_is_applied_on_export()
    {
        $this->seed(PermissionsSeeder::class);

        $user = $this->asRegularUser()->withPermissionsTo('view own contacts')->signIn();

        Contact::factory()->count(2)->state(new Sequence(
            ['first_name' => 'Same Name', 'user_id' => $user->getKey()],
            ['first_name' => 'Same Name', 'user_id' => null]
        ))->create();

        try {
            $response = $this->postJson('/api/contacts/export', [
                'type'   => 'csv',
                'period' => 'last_7_days',
            ]);

            $csvArray = $this->csvToArray($response->getFile()->getPathname());
            $this->assertEquals($response->getStatusCode(), 200);
            $this->assertCount(1, $csvArray);
        } finally {
            if (is_file($response->getFile()->getPathname())) {
                unlink($response->getFile()->getPathname());
            }
        }
    }

    public function test_filters_are_applied_on_export()
    {
        $this->signIn();

        $contacts = Contact::factory()->count(2)->create();

        $response = $this->postJson('/api/contacts/export', [
            'type'    => 'csv',
            'period'  => 'last_7_days',
            'filters' => [
            'condition'    => 'and',
                'children' => [
                    [
                        'type'  => 'rule',
                        'query' => [
                            'type'     => 'text',
                            'rule'     => 'first_name',
                            'operator' => 'equal',
                            'operand'  => '',
                            'value'    => $contacts[0]->first_name,
                        ],
                    ],
                ],
            ],
        ]);

        $csvArray = $this->csvToArray($response->getFile()->getPathname());

        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertCount(1, $csvArray);
    }
}
