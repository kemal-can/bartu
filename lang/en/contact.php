<?php

return [
    'contact'                 => 'Contact',
    'contacts'                => 'Contacts',
    'convert'                 => 'Convert to contact',
    'create'                  => 'Create Contact',
    'add'                     => 'Add Contact',
    'total'                   => 'Total Contacts',
    'import'                  => 'Import Contacts',
    'export'                  => 'Export Contacts',
    'no_companies_associated' => 'The contact has no companies associated.',
    'no_deals_associated'     => 'The contact has no deals associated.',
    'works_at'                => ':job_title at :company',
    'create_with'             => 'Create Contact with :name',
    'associate_with'          => 'Associate Contact with :name',
    'associated_company'      => 'Associated contact company',
    'dissociate'              => 'Dissociate contact',

    'exists_in_trash_by_email' => 'Contact with this email address already exists in the trash, you won\'t be able to create a new contact with the same email address, would you like to restore the trashed contact?',

    'associate_field_info' => 'Use this field to find and associate exisiting contact instead of creating new one.',

    'cards' => [
        'recently_created'      => 'Recently created contacts',
        'recently_created_info' => 'Showing the last :total created contacts in the last :days days, sorted by newest on top.',
        'by_day'                => 'Contacts by day',
        'by_source'             => 'Contacts by source',
    ],

    'notifications' => [
        'assigned' => 'You have been assigned to a contact :name by :user',
    ],

    'filters' => [
        'my'                   => 'My Contacts',
        'my_recently_assigned' => 'My Recently Assigned Contacts',
    ],

    'mail_placeholders' => [
        'assigneer' => 'The user name who assigned the contact',
    ],

    'workflows' => [
      'triggers' => [
          'created' => 'Contact Created',
      ],
      'actions' => [
        'fields' => [
          'email_to_contact'       => 'Contact email',
          'email_to_owner_email'   => 'Contact owner email',
          'email_to_creator_email' => 'Contact creator email',
          'email_to_company'       => 'Contact primary company',
        ],
      ],
    ],

    'timeline' => [
        'deleted'                        => 'The contact has been deleted by :causer',
        'restored'                       => 'The contact has been restored from trash by :causer',
        'created'                        => 'The contact has been created by :causer',
        'updated'                        => 'The contact has been updated by :causer',
        'imported_via_calendar_attendee' => 'Contact imported via :user calendar because was added as attendee to an event.',
        'attached'                       => ':user associated contact',
        'detached'                       => ':user dissociated contact',
        'associate_trashed'              => 'The associated :contactName contact has been moved to trash by :user',
    ],

    'validation' => [
        'email' => [
            'unique' => 'A contact or team member with this email already exist.',
        ],
        'phone' => [
            'unique' => 'A contact with this phone number already exist.',
        ],
    ],

];
