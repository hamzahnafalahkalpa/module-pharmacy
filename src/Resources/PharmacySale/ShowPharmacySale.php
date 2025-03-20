<?php

namespace Zahzah\ModulePharmacy\Resources\PharmacySale;

use Zahzah\ModulePatient\Resources\VisitPatient\ShowVisitPatient;

class ShowPharmacySale extends ShowVisitPatient
{
    public function toArray(\Illuminate\Http\Request $request): array
    {
        $arr = [

        ];
        $view = $this->resolveNow(new ViewPharmacySale($this));
        $arr = $this->mergeArray(parent::toArray($request),$view,$arr);
        
        return $arr;
    }
}

