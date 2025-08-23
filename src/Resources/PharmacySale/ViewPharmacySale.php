<?php

namespace Hanafalah\ModulePharmacy\Resources\PharmacySale;

use Hanafalah\ModulePatient\Resources\VisitPatient\ViewVisitPatient;

class ViewPharmacySale extends ViewVisitPatient
{
    public function toArray(\Illuminate\Http\Request $request): array
    {
        $arr = [
        ];
        $arr = $this->mergeArray(parent::toArray($request), $arr);
        return $arr;
    }
}
