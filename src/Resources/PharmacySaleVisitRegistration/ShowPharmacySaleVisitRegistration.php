<?php

namespace Hanafalah\ModulePharmacy\Resources\PharmacySaleVisitRegistration;

use Hanafalah\ModulePatient\Resources\VisitRegistration\ShowVisitRegistration;

class ShowPharmacySaleVisitRegistration extends ShowVisitRegistration
{
    public function toArray(\Illuminate\Http\Request $request): array
    {
        $arr = [];
        $view = $this->resolveNow(new ViewPharmacySaleVisitRegistration($this));
        $arr = $this->mergeArray(parent::toArray($request), $view, $arr);

        return $arr;
    }
}
