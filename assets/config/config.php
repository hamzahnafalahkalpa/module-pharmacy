<?php

use Hanafalah\ModulePharmacy\{
    Commands,
};

return [
    'namespace' => 'Hanafalah\\ModulePharmacy',
    'app' => [
        'contracts' => [
            //ADD YOUR CONTRACTS HERE
        ]
    ],
    'libs' => [
        'model' => 'Models',
        'contract' => 'Contracts',
        'schema' => 'Schemas',
        'database' => 'Database',
        'data' => 'Data',
        'resource' => 'Resources',
        'migration' => '../assets/database/migrations'
    ],
    "encodings" => [
        'PHARMACY_SALE' => [
            'flag' => 'PHARMACY_SALE', 'name' => 'Kode Penjualan Resep Obat/BMHP'
        ],
    ],
    'database' => [
        'models' => [
        ]
    ],
    'examinations' => [
    ],
    'commands' => [
        Commands\InstallMakeCommand::class
    ],
];
