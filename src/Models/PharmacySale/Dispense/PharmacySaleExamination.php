<?php

namespace Hanafalah\ModulePharmacy\Models\PharmacySale\Dispense;

use Hanafalah\ModuleExamination\Models\Examination\Assessment\Assessment;

class PharmacySaleExamination extends Assessment
{
    protected $table = 'assessments';
    public $specific = [
        'consument',
        'dispense'
    ];
}
