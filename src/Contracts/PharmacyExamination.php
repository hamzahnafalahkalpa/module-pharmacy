<?php

namespace Hanafalah\ModulePharmacy\Contracts;

use Hanafalah\ModuleExamination\Contracts\Examination;

interface PharmacyExamination extends Examination
{
    public function commitExamination(): array;
}
