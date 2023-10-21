<?php

return [
    [
        'name' => 'Marketplace',
        'flag' => 'marketplace.index',
    ],

    [
        'name' => 'Stores',
        'flag' => 'marketplace.store.index',
        'parent_flag' => 'marketplace.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'marketplace.store.create',
        'parent_flag' => 'marketplace.store.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'marketplace.store.edit',
        'parent_flag' => 'marketplace.store.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'marketplace.store.destroy',
        'parent_flag' => 'marketplace.store.index',
    ],
    [
        'name' => 'View',
        'flag' => 'marketplace.store.view',
        'parent_flag' => 'marketplace.store.index',
    ],
    [
        'name' => 'Update balance',
        'flag' => 'marketplace.store.revenue.create',
        'parent_flag' => 'marketplace.store.index',
    ],

    [
        'name' => 'Withdrawals',
        'flag' => 'marketplace.withdrawal.index',
        'parent_flag' => 'marketplace.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'marketplace.withdrawal.edit',
        'parent_flag' => 'marketplace.withdrawal.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'marketplace.withdrawal.destroy',
        'parent_flag' => 'marketplace.withdrawal.index',
    ],

    [
        'name' => 'Vendors',
        'flag' => 'marketplace.vendors.index',
        'parent_flag' => 'marketplace.index',
    ],
    [
        'name' => 'Unverified vendors',
        'flag' => 'marketplace.unverified-vendors.index',
        'parent_flag' => 'marketplace.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'marketplace.unverified-vendors.edit',
        'parent_flag' => 'marketplace.unverified-vendors.index',
    ],

    [
        'name' => 'Settings',
        'flag' => 'marketplace.settings',
        'parent_flag' => 'marketplace.index',
    ],

    [
        'name' => 'Reports',
        'flag' => 'marketplace.reports',
        'parent_flag' => 'marketplace.index',
    ],
];
