<?php

namespace Hanafalah\ModulePharmacy\Data;

use Illuminate\Support\Str;
use Hanafalah\ModulePatient\Data\VisitPatientData;
use Hanafalah\ModulePharmacy\Contracts\Data\PharmacySaleData as DataPharmacySaleData;

class PharmacySaleData extends VisitPatientData implements DataPharmacySaleData{
    public static function before(array &$attributes){
        $new = static::new();
        $attributes['flag'] ??= 'PharmacySale';
        $attributes['visited_at'] ??= now();

        if (isset($attributes['reference_type']) && $attributes['reference_type'] === 'VisitExamination' && !isset($attributes['id'])) {
            $new->generatePharmacySale($attributes);
        }
        parent::before($attributes);
    }

    protected function generatePharmacySale(array &$attributes): void{
        $new = static::new();
        $visit_examination = $attributes['visit_examination_model'] ?? $new->VisitExaminationModel()->with('visitRegistration.visitPatient')->findOrFail($attributes['visit_examination_id']);

        if (!$visit_examination->relationLoaded('assessments')){
            $examinations = config('module-pharmacy.examinations', []);
            $keys = array_keys($examinations);
            $morphs = [];
            foreach ($keys as $key) $morphs[] = Str::studly($key);
            
            $visit_examination->load([
                'assessments' => function($query) use ($morphs) {
                    $query->whereIn('morph', $morphs);
                }
            ]);

            $prescription = [];
            foreach ($visit_examination->assessments as $assessment) {
                $morph = $assessment->morph;
                $prescription[Str::snake($morph)] ??= [
                    'data' => []
                ];
                $exam = $assessment->exam;
                $data = [
                    'parent_id' => $assessment->getKey(),
                    'exam' => []
                ];
                switch ($morph) {
                    case 'MedicinePrescription':
                    case 'MedicToolPrescription':
                        unset($exam['card_stock']['id']);
                        unset($exam['card_stock']['stock_movement']['id']);
                    break;
                    break;
                    case 'MixPrescription':
                        foreach ($exam['card_stocks'] as &$exam_card_stock) {
                            unset($exam_card_stock['id']);
                            unset($exam_card_stock['stock_movement']['id']);
                        }
                    break;
                }
                $data['exam'] = $exam;
                $prescription[$morph]['data'][] = $data;
            }
        }

        $visit_registration = $visit_examination->visitRegistration;
        $visit_patient = $visit_registration->visitPatient;
        $medic_service = $new->MedicServiceModel()->where('label','INSTALASI FARMASI')->first();
        if (isset($medic_service)) {
            $attributes = array_merge($attributes, [
                'patient_id' => $visit_patient->patient_id,
                'patient_type_service_id' => $visit_patient->patient_type_service_id,
                'payer_id' => $visit_patient->payer_id,
                'reference_id'   => $visit_examination->getKey(),
                'reference_type' => $visit_examination->getMorphClass(),
                'visit_registrations' => [
                    [
                        "medic_service_id" => $medic_service->getKey(),
                        "visit_examination" => [
                            'examination' => [
                                "prescription" => $prescription
                            ]
                        ]
                    ]
                ]
            ]);
        }
    }
}