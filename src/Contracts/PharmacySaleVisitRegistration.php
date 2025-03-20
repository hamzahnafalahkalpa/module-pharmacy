<?php

namespace Zahzah\ModulePharmacy\Contracts;

use Zahzah\ModulePatient\Contracts\VisitRegistration;

interface PharmacySaleVisitRegistration extends VisitRegistration{
    public function storePharmacySaleVisitRegistration(): array;
}
