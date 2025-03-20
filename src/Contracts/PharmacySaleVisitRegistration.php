<?php

namespace Hanafalah\ModulePharmacy\Contracts;

use Hanafalah\ModulePatient\Contracts\VisitRegistration;

interface PharmacySaleVisitRegistration extends VisitRegistration
{
    public function storePharmacySaleVisitRegistration(): array;
}
