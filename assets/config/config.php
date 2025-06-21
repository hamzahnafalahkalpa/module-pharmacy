<?php

use Hanafalah\ModulePharmacy\{
    Models as ModulePharmacyModels,
    Commands as ModulePharmacyCommands,
    Schemas
};
use Hanafalah\ModulePharmacy\Contracts;

return [
    'commands' => [
        // ModulePharmacyCommands\InstallMakeCommand::class
    ],
    "encodings" => [
        'PHARMACY_SALE' => [
            'flag' => 'PHARMACY_SALE', 'name' => 'Kode Penjualan Resep Obat/BMHP'
        ],
    ],
    'app' => [
        'contracts' => [
            //ADD YOUR CONTRACTS HERE
        ],
    ],
    'libs' => [
        'model' => 'Models',
        'contract' => 'Contracts'
    ],
    'database' => [
        'models' => [
        ]
    ],
    'examinations' => [
    ]
];
