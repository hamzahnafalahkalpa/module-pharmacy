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
        'PHARMACY_SALE' => ['flag' => 'PHARMACY_SALE', 'name' => 'Kode Penjualan Resep Obat/BMHP'],
    ],
    'contracts' => [
        'pharmacy_examination'             => Contracts\PharmacyExamination::class,
        'pharmacy_sale_examination'        => Contracts\PharmacySaleExamination::class,
        'pharmacy_sale_visit_registration' => Contracts\PharmacySaleVisitRegistration::class
    ],
    'database' => [
        'models' => [
            'PharmacySale'                  => ModulePharmacyModels\PharmacySale\PharmacySale::class,
            'PharmacySaleVisitRegistration' => ModulePharmacyModels\PharmacySale\PharmacySaleVisitRegistration::class,
            'PharmacySaleExamination'       => ModulePharmacyModels\PharmacySale\Dispense\PharmacySaleExamination::class
        ]
    ],
    'examinations' => [
        'PharmacySaleExamination' => [
            'schema' => Schemas\Dispense\PharmacySaleExamination::class
        ]
    ]
];
