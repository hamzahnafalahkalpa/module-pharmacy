<?php

namespace Zahzah\ModulePharmacy\Resources\PharmacySaleVisitRegistration;

use Zahzah\ModulePatient\Resources\VisitRegistration\ViewVisitRegistration;

class ViewPharmacySaleVisitRegistration extends ViewVisitRegistration
{
    public function toArray(\Illuminate\Http\Request $request): array
    {
        $arr = [
        ];
        $arr = $this->mergeArray(parent::toArray($request),$arr);
        
        return $arr;
    }
}

