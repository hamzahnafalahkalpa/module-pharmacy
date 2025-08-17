<?php

namespace Hanafalah\ModulePharmacy\Schemas;

use Hanafalah\ModulePharmacy\Contracts\Schemas\PharmacySale as ContractsPharmacySale;
use Illuminate\Database\Eloquent\Model;
use Hanafalah\ModulePatient\Schemas\VisitPatient as SchemasVisitPatient;
use Hanafalah\ModulePharmacy\Contracts\Data\PharmacySaleData;
use Hanafalah\ModulePharmacy\Enums\PharmacySale\Activity;
use Hanafalah\ModulePharmacy\Enums\PharmacySale\ActivityStatus;

class PharmacySale extends SchemasVisitPatient implements ContractsPharmacySale
{
    protected string $__entity    = 'PharmacySale';
    public $pharmacy_sale;

    protected array $__cache = [
        'show' => [
            'name'     => 'pharmacy_sale',
            'tags'     => ['pharmacy_sale', 'pharmacy_sale-show'],
            'duration' => 60
        ]
    ];

    public function prepareStorePharmacySale(PharmacySaleData $pharmacy_sale_dto): Model{
        $pharmacy_sale_model = $this->prepareStoreVisitPatient($pharmacy_sale_dto);

        $pharmacy_sale_model->pushActivity(Activity::PHARMACY_SALE_VISIT->value, [ActivityStatus::PHARMACY_SALE_VISIT_DRAFT->value]);
        $this->preparePushLifeCycleActivity($pharmacy_sale_model, $pharmacy_sale_model, 'PHARMACY_SALE_VISIT', ['PHARMACY_SALE_VISIT_DRAFT']);

        $pharmacy_sale_model->properties = $attributes['properties'] ?? [];

        $this->initTransaction($pharmacy_sale_dto, $pharmacy_sale_model)
             ->initPaymentSummary($pharmacy_sale_dto, $pharmacy_sale_model);

        $pharmacy_sale_model->save();
        return $pharmacy_sale_model;
    }
}
