<?php

namespace Zahzah\ModulePharmacy\Enums\PharmacySaleVisitRegistration;

enum ActivityStatus: int{
    case PHARMACY_FLOW_QUEUE       = 1;
    case PHARMACY_FLOW_FRONTLINE   = 2;
    case PHARMACY_FLOW_DISPENSE    = 3;
    case PHARMACY_FLOW_PENYERAHAN  = 4;
}