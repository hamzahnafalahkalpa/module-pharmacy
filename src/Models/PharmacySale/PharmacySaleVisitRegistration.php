<?php

namespace Hanafalah\ModulePharmacy\Models\PharmacySale;

use Hanafalah\LaravelSupport\Concerns\Support\HasActivity;
use Hanafalah\ModulePatient\Models\EMR\VisitRegistration;
use Hanafalah\ModulePharmacy\Enums\PharmacySaleVisitRegistration\{
    Activity,
    ActivityStatus
};

class PharmacySaleVisitRegistration extends VisitRegistration
{
    use HasActivity;
    protected $table = 'visit_registrations';

    public function getForeignKey(){
        return 'visit_registration_id';
    }

    public function viewUsingRelation(): array{
        return [
            'medicService',
            'patientType',
            'visitExamination',
            'visitPatient' => function ($query) {
                $query->with([
                    'patient',
                    'transaction.consument'
                ]);
            }
        ];
    }

    public function showUsingRelation(): array{
            return [
            'visitPatient' => function ($query) {
                $query->with([
                    'patient' => function ($query) {
                        $query->with(['reference.cardIdentities', 'cardIdentities']);
                    },
                    'transaction.consument',
                    'services'
                ]);
            },
            'medicService.service',
            'visitExamination',
            'patientType',
            'headDoctor'
        ];
    }

    public array $activityList = [
        Activity::PHARMACY_FLOW->value . '_' . ActivityStatus::PHARMACY_FLOW_QUEUE->value       => ['flag' => 'pharmacy_flow_frontline', 'message' => 'Dalam antrian kefarmasian'],
        Activity::PHARMACY_FLOW->value . '_' . ActivityStatus::PHARMACY_FLOW_FRONTLINE->value   => ['flag' => 'pharmacy_flow_frontline', 'message' => 'Masuk tahap frontline'],
        Activity::PHARMACY_FLOW->value . '_' . ActivityStatus::PHARMACY_FLOW_DISPENSE->value    => ['flag' => 'pharmacy_flow_dispense', 'message' => 'Dilakukan dispense'],
        Activity::PHARMACY_FLOW->value . '_' . ActivityStatus::PHARMACY_FLOW_PENYERAHAN->value  => ['flag' => 'pharmacy_flow_dispense', 'message' => 'Telah dilakukan penyerahan']
    ];
}
