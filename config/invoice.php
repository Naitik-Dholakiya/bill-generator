<?php

return [

    // Template used when a user has no template set (or an unknown one)
    'default' => 'pdf',

    // key (stored in usermaster.invoice_template) => label + Blade view.
    // To add a new layout: create the Blade view registered below.
    // and register it here. Only keys listed here can be assigned.
    'templates' => [
        'pdf' => [
            'label' => 'General PDF Invoice',
            'view'  => 'invoice.pdf',
        ],
        'jewellery' => [
            'label' => 'Jewellery Voucher',
            'view'  => 'invoice.jewellery-invoice',
        ],
    ],
];