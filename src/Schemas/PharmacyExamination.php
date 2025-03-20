<?php

namespace Zahzah\ModulePharmacy\Schemas;

use Zahzah\ModulePharmacy\Contracts\PharmacyExamination as ContractsPharmacyExamination;
use Gii\ModuleExamination\Schemas\Examination;
use Illuminate\Database\Eloquent\Model;
use Zahzah\ModulePatient\Enums\VisitPatient\VisitStatus;
use Zahzah\ModulePatient\Enums\VisitRegistration\RegistrationStatus;
use Zahzah\ModulePharmacy\Enums\{
    PharmacySaleVisitRegistration\Activity as VisitRegistrationActivity,
    PharmacySaleVisitRegistration\ActivityStatus as VisitRegistrationActivityStatus
};

class PharmacyExamination extends Examination implements ContractsPharmacyExamination{
    protected string $__entity = 'PharmacyExamination';
    public static $pharmacy_examination;

    public function commitExamination(): array{
        return $this->transaction(function(){
            $attributes ??= request()->all();
            $this->initializeExamination($attributes);
            $practitioner_schema          = $this->appPractitionerEvaluationSchema();
            $practitioner                 = $practitioner_schema->prepareCommitPractitionerEvaluation($attributes);
            $visit_examination            = $practitioner->visitExamination;
            $visit_examination->is_commit = true;
            $visit_examination->save();
            if (isset($visit_examination->is_commit) && $visit_examination->is_commit){
                $visit_registration         = $this->PharmacySaleVisitRegistrationModel()->find($visit_examination->visit_registration_id);
                $visit_registration->status = RegistrationStatus::COMPLETED->value;
                $visit_registration->save();
                $visit_patient = $visit_registration->visitPatient;

                $this->toComplete($visit_registration,$visit_patient);                
                
                $visit_patient->status = VisitStatus::COMPLETED->value;
                $visit_patient->save();

                $card_stocks = $visit_patient->cardStocks;
                //UPDATING STOCK
                if (isset($card_stocks) && count($card_stocks) > 0){
                    foreach ($card_stocks as $card_stock) {
                        $card_stock->reported_at = now();
                        $card_stock->save();
                    }
                }
                $visit_patient->reporting();
            }
            return $practitioner_schema->showPractitionerEvaluation($practitioner);
        });
    }

    protected function toComplete(? Model $visit_registration = null, ? Model $visit_patient = null): self{
        $visit_registration ??= $this->PharmacySaleVisitRegistrationModel()->find(static::$__visit_examination->visit_registration_id);
        $visit_patient      ??= $this->PharmacySaleModel()->find($visit_registration->visit_patient_id);

        $visit_registration->pushActivity(VisitRegistrationActivity::PHARMACY_FLOW->value,[VisitRegistrationActivityStatus::PHARMACY_FLOW_PENYERAHAN->value]);
        $this->appVisitPatientSchema()->preparePushLifeCycleActivity($visit_patient, $visit_registration,'PHARMACY_FLOW',[
            'PHARMACY_FLOW_PENYERAHAN' => $visit_registration::$activityList[VisitRegistrationActivity::PHARMACY_FLOW->value.'_'.VisitRegistrationActivityStatus::PHARMACY_FLOW_PENYERAHAN->value]
        ]);
        return $this;
    }
}
