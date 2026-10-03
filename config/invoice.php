<?php

return [

    // Template used when a user has no template set (or an unknown one)
    'default' => 'pdf',

    // key (stored in usermaster.invoice_template) => label + Blade view.
    // To add a new layout: create resources/views/invoices/<name>.blade.php
    // and register it here. Only keys listed here can be assigned.
    'templates' => [
        'pdf' => [
            'label' => 'Gold Tax Invoice',
            'view'  => 'invoice.pdf',
        ],
    ],
];