<?php

namespace Zahzah\ModulePharmacy\Contracts;

use Gii\ModuleExamination\Contracts\Examination;

interface PharmacyExamination extends Examination{
    public function commitExamination(): array;
}
