<?php

namespace Zahzah\ModulePharmacy\Schemas;

use Gii\ModuleMedicService\Enums\MedicServiceFlag;
use Illuminate\Database\Eloquent\Builder;
use Zahzah\ModulePharmacy\Contracts\PharmacySaleVisitRegistration as ContractsPharmacySaleVisitRegistration;
use Illuminate\Database\Eloquent\Model;
use Zahzah\ModulePatient\Schemas\VisitRegistration;
use Zahzah\ModulePharmacy\Enums\PharmacySale\Activity as PharmacySaleActivity;
use Zahzah\ModulePharmacy\Enums\PharmacySale\ActivityStatus as PharmacySaleActivityStatus;
use Zahzah\ModulePharmacy\Enums\PharmacySaleVisitRegistration\Activity;
use Zahzah\ModulePharmacy\Enums\PharmacySaleVisitRegistration\ActivityStatus;
use Zahzah\ModulePharmacy\Resources\PharmacySaleVisitRegistration\{
    ShowPharmacySaleVisitRegistration,
    ViewPharmacySaleVisitRegistration
};

class PharmacySaleVisitRegistration extends VisitRegistration implements ContractsPharmacySaleVisitRegistration
{
    protected string $__entity = 'PharmacySaleVisitRegistration';
    public static $pharmacy_sale_visit_registration;

    protected array $__resources = [
        'view' => ViewPharmacySaleVisitRegistration::class,
        'show' => ShowPharmacySaleVisitRegistration::class
    ];

    protected array $__cache = [
        'show' => [
            'name'     => 'pharmacy-sale-visit-registration',
            'tags'     => ['pharmacy-sale-visit-registration','pharmacy-sale-visit-registration-show'],
            'duration' => 60
        ]
    ];

    public function storePharmacySaleVisitRegistration(): array{
        return $this->transaction(function(){
            return $this->showVisitRegistration($this->prepareStorePharmacySaleVisitRegistration());
        });
    }

    protected function showUsingRelation(){
        return [
            'visitPatient' => function($query){
                $query->with([
                    'patient' => function($query){
                        $query->with(['reference.cardIdentities','cardIdentities']);
                    },
                    'transaction.consument',
                    'services'
                ]);
            }, 'medicService.service','visitExamination',
            'patientType', 'headDoctor'
        ];
    }

    public function viewUsingRelation(): array{
        return [
            'medicService','patientType','visitExamination','visitPatient' => function($query){
                $query->with([
                    'patient', 'transaction.consument'
                ]);
            }
        ];
    }

    public function visitRegistration(mixed $conditionals = null): Builder{
        $medic_service_id  = $this->MedicServiceModel()->where('name','Instalasi Farmasi')->first()->getKey();

        return $this->VisitRegistrationModel()
                    ->conditionals($conditionals ?? [])
                    ->with('visitExamination')
                    ->when(isset(request()->search_value),function($query){
                        request()->merge([
                            'search_medical_record'  => request()->search_value,
                            'search_name'            => request()->search_value,
                            'search_nik'             => request()->search_value,
                            'search_crew_id'         => request()->search_value,
                            'search_dob'             => request()->search_value,
                            'search_consument_name'  => request()->search_value,
                            'search_consument_phone' => request()->search_value,
                            'search_value'           => null
                        ]);
                        $query->whereHasMorph('visitPatient',[$this->PharmacySaleModelMorph()],function($query){
                            $query->whereHas('patient',fn ($q) => $q->withParameters('or'))
                                  ->orWhereHas('transaction.consument',function($q){
                                      $q->whereLike('name',request()->search_value);
                                  });
                        });
                    })
                    ->when(isset(request()->search_created_at),function($query){
                        $query->withParameters();
                    })->where('medic_service_id',$medic_service_id)->with('visitExamination');
    }

    public function prepareStorePharmacySaleVisitRegistration(? array $attributes = null): Model{
        $attributes ??= request()->all();

        //SET DEFAULT PATIENT TYPE
        if (!isset($attributes['patient_type_id'])){
            $patient_type = $this->PatientTypeModel()->where('name','Umum')->firstOrFail();
            $attributes['patient_type_id'] = $patient_type->getKey();
        }

        $pharmacy_visit_registration = parent::prepareStoreVisitRegistration([
            'visit_patient'     => [
                'patient_id'    => $attributes['patient_id'] ?? null,
                'flag'          => $this->PharmacySaleModel()::PHARMACY_SALE_VISIT,
                'reported_at'   => null,
            ],
            'patient_type_id'   => $attributes['patient_type_id'] ?? null,
            'medic_service_id'  => $this->MedicServiceModel()->where('flag',MedicServiceFlag::PHARMACY->value)->where('name','Instalasi Farmasi')->firstOrFail()->service->getKey(),
            'medic_services'    => [],
            'id' => $attributes['id'] ?? null
        ]);
        $pharmacy_sale               = $pharmacy_visit_registration->visitPatient;

        //SETUP PATIENT AS CONSUMENT
        if (isset($attributes['patient_id'])){
            $patient = $this->PatientModel()->findOrFail($attributes['patient_id']);
            $attributes['consument'] = [
                'phone'          => null,
                'name'           => $patient->prop_people['name'],
            ];
            $consument_attr                   = &$attributes['consument'];
            $consument_attr['reference_id']   = $patient->getKey();
            $consument_attr['reference_type'] = $patient->getMorphClass();
            $attributes['payer_id']           = $patient->prop_company['id'] ?? null;
        }

        if (isset($attributes['consument']) && isset($attributes['consument']['name'])){
            $transaction   = $pharmacy_sale->transaction;

            if (isset($attributes['patient_id'])){
                $consument = $this->ConsumentModel()->updateOrCreate([
                    'reference_id'   => $attributes['consument']['reference_id'] ?? null,
                    'reference_type' => $attributes['consument']['reference_type'] ?? null
                ],[
                    'phone' => $attributes['consument']['phone'] ?? null,
                    'name'           => $attributes['consument']['name'],
                ]);

                $consument->setAttribute('prop_patient',$patient->getPropsKey());
                $consument->save();
            }else{
                $consument = $this->ConsumentModel()->updateOrCreate([
                    'phone' => $attributes['consument']['phone'] ?? null,
                    'name'           => $attributes['consument']['name'],
                    'reference_id'   => $attributes['consument']['reference_id'] ?? null,
                    'reference_type' => $attributes['consument']['reference_type'] ?? null
                ]);
            }

            $transaction->transactionHasConsument()->firstOrCreate([
                'consument_id' => $consument->getKey()
            ]);

            $props = [
                'id'    => $consument->getKey(),
                'name'  => $consument->name,
                'phone' => $consument->phone
            ];
            if (count($consument->getPropsKey() ?? []) > 0){
                $props = $this->mergeArray($props,$consument->getPropsKey());
            }

            $pharmacy_sale->setAttribute('prop_consument',$props);
            $pharmacy_sale->save();

            $transaction->consument_name = $consument->name;
            $transaction->setAttribute('prop_consument',$props);

            $transaction->reported_at = now();
            $transaction->save();
        }


        $pharmacy_visit_registration = $this->PharmacySaleVisitRegistrationModel()->findOrFail($pharmacy_visit_registration->getKey());
        $pharmacy_sale->pushActivity(PharmacySaleActivity::PHARMACY_SALE_VISIT->value,[PharmacySaleActivityStatus::PHARMACY_SALE_VISIT_DRAFT->value,PharmacySaleActivityStatus::PHARMACY_SALE_VISIT_PROCESSED->value]);
        $this->toFrontline($pharmacy_sale,$pharmacy_visit_registration);
        $pharmacy_sale->refresh();

        return static::$pharmacy_sale_visit_registration = $pharmacy_visit_registration;
    }

    protected function toFrontline(? Model $visit_patient = null, ? Model $pharmacy_visit_registration = null): self{
        $pharmacy_visit_registration ??= $this->PharmacySaleVisitRegistrationModel()->find(static::$__visit_registration->getKey());
        $visit_patient               ??= $this->PharmacySaleModel()->find($pharmacy_visit_registration->visit_patient_id);
        $pharmacy_visit_registration->pushActivity(Activity::PHARMACY_FLOW->value,[ActivityStatus::PHARMACY_FLOW_QUEUE->value, ActivityStatus::PHARMACY_FLOW_FRONTLINE->value]);
        $this->appPharmacySaleSchema()->preparePushLifeCycleActivity($visit_patient,$pharmacy_visit_registration,'PHARMACY_FLOW',[
            'PHARMACY_FLOW_FRONTLINE' => $pharmacy_visit_registration::$activityList[Activity::PHARMACY_FLOW->value.'_'.ActivityStatus::PHARMACY_FLOW_FRONTLINE->value]
        ]);
        return $this;
    }
}
