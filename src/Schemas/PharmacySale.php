<?php

namespace Hanafalah\ModulePharmacy\Schemas;

use Hanafalah\ModulePharmacy\Contracts\PharmacySale as ContractsPharmacySale;
use Illuminate\Database\Eloquent\Model;
use Hanafalah\ModulePatient\Enums\VisitPatient\VisitStatus;
use Hanafalah\ModulePatient\Schemas\VisitPatient as SchemasVisitPatient;
use Hanafalah\ModulePharmacy\Enums\PharmacySale\Activity;
use Hanafalah\ModulePharmacy\Enums\PharmacySale\ActivityStatus;
use Hanafalah\ModulePharmacy\Resources\PharmacySale\{
    ShowPharmacySale,
    ViewPharmacySale
};

class PharmacySale extends SchemasVisitPatient implements ContractsPharmacySale
{
    protected string $__entity    = 'PharmacySale';
    public static $pharmacy_sale;

    protected array $__cache = [
        'show' => [
            'name'     => 'pharmacy_sale',
            'tags'     => ['pharmacy_sale', 'pharmacy_sale-show'],
            'duration' => 60
        ]
    ];

    public function prepareStorePharmacySale(?array $attributes = null): Model
    {
        $attributes ??= request()->all();

        if (isset($attributes['patient_id'])) {
            $patient = $this->PatientModel()->find($attributes['patient_id']);
            if (!isset($patient)) throw new \Exception('Patient not found.', 422);

            $patient_id = $patient->getKey();
            $attributes['payer_id'] ??= $patient->prop_company['id'] ?? null;
        } else {
            $patient_id = null;
        }

        if (isset($attributes['id'])) {
            $guard = ['id' => $attributes['id']];
        } else {
            $guard = [
                'status'         => VisitStatus::ACTIVE->value,
                'patient_id'     => $patient_id,
                'reported_at'    => null,
                'reference_type' => $attributes['reference_type'] ?? null,
                'reference_id'   => $attributes['reference_id'] ?? null
            ];
        }
        $add = [
            'visited_at' => now(),
            'status'     => VisitStatus::ACTIVE->value
        ];
        $pharmacy_sale = $this->PharmacySaleModel()->withoutGlobalScopes(['PHARMACY_VISIT', 'CLINICAL_VISIT']);
        $pharmacy_sale = (!isset($attributes['reference_id']))
            ? $pharmacy_sale->create($guard, $add)
            : $pharmacy_sale->updateOrCreate($guard, $add);

        $pharmacy_sale->pushActivity(Activity::PHARMACY_SALE_VISIT->value, [ActivityStatus::PHARMACY_SALE_VISIT_DRAFT->value]);
        $this->preparePushLifeCycleActivity($pharmacy_sale, $pharmacy_sale, 'PHARMACY_SALE_VISIT', ['PHARMACY_SALE_VISIT_DRAFT']);

        $pharmacy_sale->properties = $attributes['properties'] ?? [];
        if (isset($patient)) $pharmacy_sale->prop_patient = $patient->getOriginal()['props'];

        $this->updatePaymentSummary($pharmacy_sale, $attributes, $patient ?? null, 'Total Tagihan Resep')
            ->createAgent($pharmacy_sale, $attributes)
            ->createPatientType($pharmacy_sale, $attributes);

        $pharmacy_sale->save();
        if ($pharmacy_sale->wasRecentlyCreated) {
            $this->flushTagsFrom('show');
        }
        return $pharmacy_sale;
    }
}
