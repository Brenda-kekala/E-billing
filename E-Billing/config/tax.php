<?php

return [
    // Turnover Tax
    'tot_rate' => 0.015,
    'tot_min'  => 1000000,
    'tot_max'  => 25000000,

    // Late penalties for TOT (verify on kra.go.ke)
    'late_filing_fee'       => 1000,
    'late_payment_penalty'  => 0.05,
    'late_payment_interest' => 0.01,

    // Individual self-assessment (verify on kra.go.ke)
    'bands' => [
        ['limit' => 288000,  'rate' => 0.10],
        ['limit' => 100000,  'rate' => 0.25],
        ['limit' => 5612000, 'rate' => 0.30],
        ['limit' => 3600000, 'rate' => 0.325],
        ['limit' => null,    'rate' => 0.35],   // everything above 9,600,000

    
    ],
    'personal_relief'       => 28800,
    'instalment_threshold'  => 40000,
    'sa_late_filing_rate'   => 0.05,
    'sa_late_filing_min'    => 2000,

    'occupations' => [
    'trader'       => 'Trader / Shopkeeper',
    'farmer'       => 'Farmer',
    'freelancer'   => 'Freelancer / Consultant',
    'transport'    => 'Transport (boda boda, taxi)',
    'professional' => 'Professional services (e.g. plumber, tailor)',
    'other'        => 'Other',
    ],

    'income_categories' => [
    'sales'          => 'Daily / shop sales',
    'wholesale'      => 'Wholesale / bulk order',
    'services'       => 'Services rendered',
    'livestock'      => 'Livestock / dairy sales',
    'transport'      => 'Transport fares',
    'freelance'      => 'Freelance / project payment',
    'rent'           => 'Rental income',
    'commission'     => 'Commission / referral fee',
    'construction'   => 'Construction / artisan job',
    'salon_beauty'   => 'Salon / beauty services',
    'food_catering'  => 'Food / catering',
    'online_sales'   => 'Online / social media sales',
    'other'          => 'Other',
],
    'admin_code' => env('ADMIN_REGISTRATION_CODE'),
];