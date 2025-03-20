<?php

namespace Zahzah\ModulePharmacy\Resources\PharmacySale;

use Zahzah\ModulePatient\Resources\VisitPatient\ViewVisitPatient;

class ViewPharmacySale extends ViewVisitPatient
{
    public function toArray(\Illuminate\Http\Request $request): array
    {
        $arr = [
            'consument'  => $this->relationValidation('consument',function(){
                return $this->consument->toViewApi();
            })
        ];
        $arr = $this->mergeArray(parent::toArray($request),$arr);
        
        return $arr;
    }
}

