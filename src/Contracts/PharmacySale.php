<?php

namespace Hanafalah\ModulePharmacy\Contracts;

use Illuminate\Database\Eloquent\Model;
use Hanafalah\LaravelSupport\Contracts\DataManagement;

interface PharmacySale extends DataManagement
{
    public function prepareStorePharmacySale(?array $attributes = null): Model;
}
