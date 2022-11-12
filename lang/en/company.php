<?php

return [
    'company'                => 'Company',
    'companies'              => 'Companies',
    'add'                    => 'Add Company',
    'dissociate'             => 'Dissociate Company',
    'child'                  => 'Child Company | Child Companies',
    'create'                 => 'Create Company',
    'export'                 => 'Export Companies',
    'total'                  => 'Total Companies',
    'import'                 => 'Import Companies',
    'create_with'            => 'Create Company with :name',
    'associate_with'         => 'Associate Company with :name',
    'associate_field_info'   => 'Use this field to find and associate exisiting company instead of creating new one.',
    'no_contacts_associated' => 'The company has no contacts associated.',
    'no_deals_associated'    => 'The company has no deals associated.',

    'exists_in_trash_by_email' => 'Company with this email address already exists in the trash, you won\'t be able to create a new company with the same email address, would you like to restore the trashed company?',

    'exists_in_trash_by_name' => 'Company with the same name already exists in the trash, would you like to restore the trashed company?',

    'notifications' => [
        'assigned' => 'You have been assigned to a company :name by :user',
    ],

    'cards' => [
        'by_source' => 'Companies by source',
        'by_day'    => 'Companies by day',
    ],

    'settings' => [
        'automatically_associate_with_contacts'      => 'Automatically create and associate companies with contacts',
        'automatically_associate_with_contacts_info' => 'Automatically associate contacts with companies based on a contact email address and a company domain.',
    ],

    'industry' => [
        'industries' => 'Industries',
        'industry'   => 'Industry',
    ],

    'filters' => [
        'my'                   => 'My Companies',
        'my_recently_assigned' => 'My Recently Assigned Companies',
    ],

    'mail_placeholders' => [
        'assigneer' => 'The user name who assigned the company',
    ],

    'workflows' => [
      'triggers' => [
          'created' => 'Company Created',
      ],
      'actions' => [
        'fields' => [
          'email_to_company'       => 'Company email',
          'email_to_owner_email'   => 'Company owner email',
          'email_to_creator_email' => 'Company creator email',
          'email_to_contact'       => 'Company primary contact',
        ],
      ],
    ],

    'timeline' => [
        'deleted'  => 'The company has been deleted by :causer',
        'restored' => 'The company has been restored from trash by :causer',
        'created'  => 'Company has been created by :causer',
        'updated'  => 'Company has been updated by :causer',

        'attached' => ':user associated company',
        'detached' => ':user dissociated company',

        'associate_trashed' => 'The associated :companyName company has been moved to trash by :user',
    ],

    'validation' => [
        'email' => [
            'unique' => 'A company with this email already exist.',
        ],
    ],
];
