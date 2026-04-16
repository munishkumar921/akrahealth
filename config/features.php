<?php

/* add only missing features */
return [
    'starter' => [
        'coordination' => [
            'doctor.coordination.index',
            'doctor.coordination.create',
            'doctor.coordination.store',
            'doctor.coordination.show',
            'doctor.coordination.edit',
            'doctor.coordination.update',
            'doctor.coordination.destroy',
        ],
        'insurance' => [
            'doctor.insurance.index',
            'doctor.insurance.store',
            'doctor.insurance.create',
            'doctor.insurance.show',
            'doctor.insurance.update',
            'doctor.insurance.destroy',
            'doctor.insurance.edit'
        ],
        'billing' => [
            'doctor.billing.index',
            'doctor.billing.store',
            'doctor.billing.create',
            'doctor.billing.show',
            'doctor.billing.update',
            'doctor.billing.destroy',
            'doctor.billing.edit'
        ],
    ],

    'growth' => [],

    'pro' => [],

    'trial' => [],
];
