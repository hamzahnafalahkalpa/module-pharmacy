<?php

namespace Hanafalah\ModulePharmacy\Data;

use Illuminate\Support\Str;
use Hanafalah\ModulePatient\Data\VisitPatientData;
use Hanafalah\ModulePharmacy\Contracts\Data\PharmacySaleData as DataPharmacySaleData;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapName;

class PharmacySaleData extends VisitPatientData implements DataPharmacySaleData{
    #[MapName('visit_examination_model')]
    #[MapInputName('visit_examination_model')]
    public ?object $visit_examination_model = null;

    public static function before(array &$attributes){
        $new = static::new();
        $attributes['flag'] ??= 'PharmacySale';
        $attributes['visited_at'] ??= now();

        if (isset($attributes['reference_type']) && $attributes['reference_type'] === 'VisitExamination' && !isset($attributes['id'])) {
            $new->generatePharmacySale($attributes);
        }elseif(!isset($attributes['id'])){
            $medic_service = $new->MedicServiceModel()->where('label','INSTALASI FARMASI')->firstOrFail();
            $patient_type_service_id = $new->PatientTypeServiceModel()->where('label','UMUM')->firstOrFail()->getKey();
            if (isset($medic_service)) {
                $attributes = array_merge_recursive($attributes, [
                    "patient_type_service_id" => $patient_type_service_id,
                    'visit_registration' => [
                        "medic_service_id" => $medic_service->getKey(),
                        "visit_examination" => [
                            "id"=> null
                        ]
                    ]
                ]);
            }
        }
        parent::before($attributes);
    }

    protected function generatePharmacySale(array &$attributes): void{
        // $new = static::new();
        $visit_examination = $attributes['visit_examination_model'] ?? $this->VisitExaminationModel()->with('visitRegistration.visitPatient')->findOrFail($attributes['visit_examination_id']);

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
                $morph = Str::snake($assessment->morph);
                $prescription[$morph] ??= [
                    'data' => []
                ];
                $exam = $assessment->exam;                

                if ($morph == 'basic_prescription'){
                    $new_exam = [];
                    foreach ($exam as $key => &$exam_medic) {
                        try {
                            if (!isset($exam_medic)) continue;
                            $data = [
                                'parent_id' => $assessment->getKey(),
                                'exam' => []
                            ];        
                            $this->normalizeCardStock($key, $exam_medic);
                            $data['exam'] = [
                                'type' => Str::studly($morph),
                                'is_pharmacy_sale' => true,
                                ...$exam_medic
                            ];
                            $prescription[Str::studly($morph)]['data'][] = $data;
                        } catch (\Throwable $th) {
                            //throw $th;
                        }
                    }
                }else{
                    $data = [
                        'parent_id' => $assessment->getKey(),
                        'exam' => []
                    ];
                    $this->normalizeCardStock($morph, $exam);
                    $data['exam'] = $exam;
                    $prescription[$morph]['data'][] = $data;
                }
            }
        }

        $visit_registration = $visit_examination->visitRegistration;
        $visit_patient = $visit_registration->visitPatient;
        $medic_service = $this->MedicServiceModel()->where('label','INSTALASI FARMASI')->first();
        if (isset($medic_service)) {
            $attributes = array_merge($attributes, [
                'patient_id' => $visit_patient->patient_id,
                'patient_type_service_id' => $visit_patient->patient_type_service_id,
                'payer_id' => $visit_patient->payer_id,
                'reference_id'   => $visit_examination->getKey(),
                'reference_type' => $visit_examination->getMorphClass(),
                'visit_registration' => [
                    "medic_service_id" => $medic_service->getKey(),
                    "visit_examination" => [
                        'examination' => [
                            "prescription" => $prescription
                        ]
                    ]
                ]
            ]);
        }
    }

    protected function normalizeCardStock(string $morph, array &$exam){
        switch ($morph) {
            case 'medicine_prescription':
            case 'medic_tool_prescription':
                unset($exam['card_stock']['id']);
                unset($exam['card_stock']['stock_movement']['id']);
                $exam['morph'] = $morph;
                $exam['type'] = Str::studly($morph);
                $this->initializeItemStock($exam);                
            break;
            case 'mix_prescription':
                foreach ($exam['card_stocks'] as &$exam_card_stock) {
                    unset($exam_card_stock['id']);
                    unset($exam_card_stock['stock_movement']['id']);
                    $this->initializeItemStock($exam_card_stock);
                }
            break;
        }
    }

    protected function initializeItemStock(&$exam){
        $card_stock     = &$exam['card_stock'];
        $stock_movement = &$card_stock['stock_movement'];
        $item_model = $this->ItemModel()->with(['itemStock' => function($query){
            $query->whereNull('parent_id')->with('childs');
        }])->findOrFail($card_stock['item_id']);
        $item_stock = $item_model->itemStock;
        $stock_movement['item_stock'] = [
            "id" => $item_model->getKey(),
            "funding_id" => $item_stock->funding_id,
            'funding'    => $item_stock->prop_funding,
            'stock'      => $item_stock->stock,
            'childs'     => $item_stock->childs->map(function($child){
                return [
                    "id" => $child->getKey(),
                    "funding_id" => $child->funding_id,
                    'funding'    => $child->prop_funding,
                    'stock'      => $child->stock,
                    'actual_qty' => null
                ];
            })->toArray()
        ];
    }
}