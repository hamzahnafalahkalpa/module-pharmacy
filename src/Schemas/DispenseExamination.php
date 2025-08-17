<?php

namespace Hanafalah\ModulePharmacy\Schemas;

use Hanafalah\ModuleExamination\Schemas\Examination\Assessment\Assessment;
use Hanafalah\ModulePharmacy\Contracts\Data\DispenseExaminationData;
use Illuminate\Database\Eloquent\Model;
use Hanafalah\ModulePharmacy\Contracts\Schemas\DispenseExamination as ContractsDispenseExamination;

use Hanafalah\ModulePharmacy\Enums\{
    PharmacySaleVisitRegistration\Activity as VisitRegistrationActivity,
    PharmacySaleVisitRegistration\ActivityStatus as VisitRegistrationActivityStatus
};

class DispenseExamination extends Assessment implements ContractsDispenseExamination
{
    protected string $__entity   = 'DispenseExamination';
    public $pharmacy_sale_examination_model;

    // protected function createConsument(&$consument_attr)
    // {
    //     $patient = static::$__patient;
    //     if (is_object($this->ConsumentModel())) {
    //         if (isset($consument_attr['id'])) {
    //             $guard = ['id' => $consument_attr['id']];
    //         } else {
    //             $guard = [
    //                 'name'  => $consument_attr['name'] ?? $patient->reference->name,
    //                 'phone' => $consument_attr['phone']
    //             ];
    //         }
    //         $consument = $this->ConsumentModel()->firstOrCreate($guard, [
    //             'reference_id'   => $patient->reference_id,
    //             'reference_type' => $patient->reference_type
    //         ]);
    //         $consument_attr['id'] = $consument->getKey();
    //     }
    // }

    public function prepareStore(mixed $dispense_examination_dto): Model{
        $assessment_exam = &$dispense_examination_dto->props['exam'];
        $dispense_model = parent::prepareStore($dispense_examination_dto);
        if (isset($dispense_examination_dto['consument']) && isset($attributes['consument']['name'])) {
            // $this->createConsument($attributes['consument']);
        }

        if (isset($attributes['dispense'])) {
            $dispense = &$attributes['dispense'];
            if (!isset($dispense['prescriptions'])) throw new \Exception('prescriptions is required');
            if (count($dispense['prescriptions']) == 0) throw new \Exception('prescriptions need at least one item');

            $card_stock_schema = $this->schemaContract('card_stock');

            foreach ($dispense['prescriptions'] as $prescription) {
                $prescript_assessment = $this->AssessmentModel()->findOrFail($prescription['id']);
                $card_stocks      = $prescript_assessment->card_stocks ?? [$prescript_assessment->card_stock];
                $card_stock_attrs = $prescription['card_stocks'] ?? [$prescription['card_stock']];
                $card_stock_ids = array_column($card_stocks, 'id');
                foreach ($card_stock_attrs as $item) {
                    $src = \array_search($item['id'], $card_stock_ids);
                    if (!isset($src)) throw new \Exception('Card stock not found');

                    $card_stock_schema->prepareStoreCardStock([
                        'id'              => $item['id'],
                        'direction'       => $this->MainMovementModel()::OUT,
                        'warehouse_id'    => $attributes['warehouse_id'] ?? null,
                        'pharmacy_id'     => $attributes['pharmacy_id'] ?? null,
                        'stock_movements' => $item['stock_movements']
                    ]);

                    $card_stocks[$src]['dispense'] = [
                        'stock_movements' => $item['stock_movements']
                    ];
                }
                if (isset($prescript_assessment->card_stock)) {
                    $prescript_assessment->setAttribute('card_stock', $card_stocks[0]);
                } else {
                    $prescript_assessment->setAttribute('card_stocks', $card_stocks);
                }
                $prescript_assessment->save();
            }
        }
        $this->toDispense($dispense_examination_dto);
        return $dispense_model;
    }

    protected function toDispense(DispenseExaminationData $dispense_examination_dto): self{
        $visit_patient      = $dispense_examination_dto->visit_patient_model;
        $visit_registration = $dispense_examination_dto->visit_registration_model;
        $visit_registration->pushActivity(VisitRegistrationActivity::PHARMACY_FLOW->value, [VisitRegistrationActivityStatus::PHARMACY_FLOW_DISPENSE->value]);
        $this->appVisitPatientSchema()->preparePushLifeCycleActivity($visit_patient, $visit_registration, 'PHARMACY_FLOW', [
            'PHARMACY_FLOW_DISPENSE' => $visit_registration::$activityList[VisitRegistrationActivity::PHARMACY_FLOW->value . '_' . VisitRegistrationActivityStatus::PHARMACY_FLOW_DISPENSE->value]
        ]);
        return $this;
    }
}
