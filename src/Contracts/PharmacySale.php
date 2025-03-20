<?php

namespace Zahzah\ModulePharmacy\Contracts;

use Illuminate\Database\Eloquent\Model;
use Zahzah\LaravelSupport\Contracts\DataManagement;

interface PharmacySale extends DataManagement{
    public function prepareStorePharmacySale(? array $attributes = null): Model;
}
