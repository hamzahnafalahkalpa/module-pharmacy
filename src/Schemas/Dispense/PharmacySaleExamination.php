<?php

namespace Hanafalah\ModulePharmacy\Schemas\Dispense;

use Hanafalah\ModuleExamination\Schemas\Examination\Assessment\Assessment;
use Illuminate\Database\Eloquent\Model;
use Hanafalah\ModulePharmacy\Contracts\PharmacySaleExamination as ContractsPharmacySaleExamination;

use Hanafalah\ModulePharmacy\Enums\{
    PharmacySaleVisitRegistration\Activity as VisitRegistrationActivity,
    PharmacySaleVisitRegistration\ActivityStatus as VisitRegistrationActivityStatus
};

class PharmacySaleExamination extends Assessment implements ContractsPharmacySaleExamination
{
    protected string $__entity   = 'PharmacySaleExamination';
    public $pharmacy_sale_examination_model;

    protected function createConsument(&$consument_attr)
    {
        $patient = static::$__patient;
        if (is_object($this->ConsumentModel())) {
            if (isset($consument_attr['id'])) {
                $guard = ['id' => $consument_attr['id']];
            } else {
                $guard = [
                    'name'  => $consument_attr['name'] ?? $patient->reference->name,
                    'phone' => $consument_attr['phone']
                ];
            }
            $consument = $this->ConsumentModel()->firstOrCreate($guard, [
                'reference_id'   => $patient->reference_id,
                'reference_type' => $patient->reference_type
            ]);
            $consument_attr['id'] = $consument->getKey();
        }
    }

    public function prepareStore(?array $attributes = null): Model
    {
        $attributes ??= request()->all();
        $assessment = parent::prepareStoreAssessment($attributes);
        $this->initializeExamination($attributes);
        if (isset($attributes['consument']) && isset($attributes['consument']['name'])) {
            $this->createConsument($attributes['consument']);
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
        $this->setAssessmentProp($attributes);
        static::$assessment_model->save();
        $this->toDispense();
        return $this->assessment_model = $assessment;
    }

    protected function toDispense(): self
    {
        $visit_registration = $this->PharmacySaleVisitRegistrationModel()->find(static::$__visit_examination->visit_registration_id);
        $visit_patient      = $this->PharmacySaleModel()->find($visit_registration->visit_patient_id);

        $visit_registration->pushActivity(VisitRegistrationActivity::PHARMACY_FLOW->value, [VisitRegistrationActivityStatus::PHARMACY_FLOW_DISPENSE->value]);
        $this->appVisitPatientSchema()->preparePushLifeCycleActivity($visit_patient, $visit_registration, 'PHARMACY_FLOW', [
            'PHARMACY_FLOW_DISPENSE' => $visit_registration::$activityList[VisitRegistrationActivity::PHARMACY_FLOW->value . '_' . VisitRegistrationActivityStatus::PHARMACY_FLOW_DISPENSE->value]
        ]);
        return $this;
    }
}
