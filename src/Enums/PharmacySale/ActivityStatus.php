<?php

namespace Hanafalah\ModulePharmacy\Enums\PharmacySale;

enum ActivityStatus: int
{
    case PHARMACY_SALE_VISIT_DRAFT     = 1;
    case PHARMACY_SALE_VISIT_PROCESSED = 2;
    case PHARMACY_SALE_VISIT_FINISHED  = 3;
    case PHARMACY_SALE_VISIT_CANCELLED = 4;
}
