<?php

namespace Zahzah\ModulePharmacy\Models\PharmacySale\Dispense;

use Gii\ModuleExamination\Models\Examination\Assessment\Assessment;

class PharmacySaleExamination extends Assessment {
    protected $table = 'assessments';
    public $specific = [
        'consument', 'dispense'
    ];
}