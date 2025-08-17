<?php

namespace Hanafalah\ModulePharmacy\Contracts\Schemas;

use Hanafalah\ModuleExamination\Contracts\Schemas\Examination;

interface PharmacyExamination extends Examination
{
    public function commitExamination(): array;
}
