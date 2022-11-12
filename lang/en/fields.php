<?php

return [
    'add'                 => 'Add New',
    'fields'              => 'Fields',
    'field'               => 'Field',
    'more'                => 'Show Collapsed Fields',
    'less'                => 'Less Fields',
    'updated'             => 'Updated Fields',
    'reseted'             => 'Fields are set to initial state successfully',
    'updated_field'       => 'Updated Field',
    'optional'            => '(optional)',
    'configured'          => 'Fields configured successfully',
    'no_longer_available' => 'Field no longer available',
    'new_value'           => 'New Value',
    'old_value'           => 'Old Value',
    'manage'              => 'Manage Fields',
    'hide_updated'        => 'Hide Updated Fields',
    'view_updated'        => 'View Updated Fields',
    'primary'             => 'Primary Field',
    'visible'             => 'Visible',
    'label'               => 'Label',
    'is_readonly'         => 'Read Only',

    'settings' => [
        'create'      => 'Creation Fields',
        'create_info' => 'Fields that will be displayed when any user creates a record.',

        'update'      => 'Edit / Preview Fields',
        'update_info' => 'Fields that are available and will be displayed on record edit/preview view.',

        'detail'      => 'Detail Fields',
        'detail_info' => 'Fields that are available and will be displayed on records detail.',

        'list'      => 'List Fields',
        'list_info' => 'To adjust the fields that are displayed on the list view, when viewing the :resourceName list, click the :icon icon located at the navigation bar and choose "List settings".',
    ],

    'collapsed_by_default'    => 'Collapsed by default?',
    'next_activity_date'      => 'Next Activity Date',
    'next_activity_date_info' => 'This field is read only and is automatically updated based on the record upcoming activities, indicates when the sale rep next action should be taken.',
    'is_required'             => 'Required?',
    'options'                 => 'Options',

    'validation' => [
        'invalid_name'       => 'The field ID cannot be named ":name". Try using a different ID.',
        'exist'              => 'It looks like field with this ID already exists for the resource.',
        'requires_options'   => 'Add options for this field.',
        'field_type_invalid' => 'Field type is not supported',
        'field_id_invalid'   => 'Only lowercase alpha characters (a-z) and underscore (_) are accepted.',
        'refuses_options'    => 'This field does not support options.',
    ],

    'custom' => [
        'create_option_icon' => 'Click the :icon icon to create new option.',
        'field'              => 'Custom Field',
        'create'             => 'Create New Custom Field',
        'update'             => 'Update Custom Field',
        'type'               => 'Field Type',
        'id'                 => 'Field ID',
        'id_info'            => 'Enter field ID in lowercase, only alpha characters (a-z) and underscore (_) accepted.',

        'updated' => 'Custom field successfully updated',
        'created' => 'Custom field successfully created',
        'deleted' => 'Custom field successfully deleted',
    ],

    'phones' => [
        'add'    => '+ Add another',
        'copied' => 'Phone number copied to clipboard',
        'types'  => [
            'type'   => 'Type',
            'mobile' => 'Mobile',
            'work'   => 'Work',
            'other'  => 'Other',
        ],
    ],
    'email_copied' => 'E-Mail address coppied to clipboard',
    'contacts'     => [
        'first_name'          => 'First Name',
        'last_name'           => 'Last Name',
        'email'               => 'E-Mail Address',
        'job_title'           => 'Job Name',
        'phone'               => 'Phone',
        'street'              => 'Street Address',
        'city'                => 'City',
        'state'               => 'State/Region',
        'postal_code'         => 'Postal Code',
        'owner_assigned_date' => 'Owner Assigned Date',
        'country'             => [
            'name' => 'Country',
        ],
        'source' => [
            'name' => 'Source',
        ],
        'user' => [
            'name' => 'Owner',
        ],
    ],

    'companies' => [
        'name'   => 'Name',
        'email'  => 'E-Mail Address',
        'parent' => [
            'name' => 'Parent Company',
        ],
        'phone'               => 'Phone',
        'street'              => 'Street Address',
        'city'                => 'City',
        'state'               => 'State/Region',
        'postal_code'         => 'Postal Code',
        'domain'              => 'Company domain name',
        'owner_assigned_date' => 'Owner Assigned Date',
        'country'             => [
            'name' => 'Country',
        ],
        'industry' => [
            'name' => 'Industry',
        ],
        'user' => [
            'name' => 'Owner',
        ],
        'source' => [
            'name' => 'Source',
        ],
    ],

    'deals' => [
        'name'                => 'Deal Name',
        'expected_close_date' => 'Expected Close Date',
        'amount'              => 'Amount',
        'owner_assigned_date' => 'Owner Assigned Date',
        'user'                => [
            'name' => 'Owner',
        ],
        'stage' => [
            'name' => 'Stage',
        ],
        'pipeline' => [
            'name' => 'Pipeline',
        ],
    ],
];
