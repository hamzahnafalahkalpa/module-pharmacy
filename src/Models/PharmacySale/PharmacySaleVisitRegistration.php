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

    public function getForeignKey()
    {
        return 'visit_registration_id';
    }

    public static array $activityList = [
        Activity::PHARMACY_FLOW->value . '_' . ActivityStatus::PHARMACY_FLOW_QUEUE->value       => ['flag' => 'PHARMACY_FLOW_FRONTLINE', 'message' => 'Dalam antrian kefarmasian'],
        Activity::PHARMACY_FLOW->value . '_' . ActivityStatus::PHARMACY_FLOW_FRONTLINE->value   => ['flag' => 'PHARMACY_FLOW_FRONTLINE', 'message' => 'Masuk tahap frontline'],
        Activity::PHARMACY_FLOW->value . '_' . ActivityStatus::PHARMACY_FLOW_DISPENSE->value    => ['flag' => 'PHARMACY_FLOW_DISPENSE', 'message' => 'Dilakukan dispense'],
        Activity::PHARMACY_FLOW->value . '_' . ActivityStatus::PHARMACY_FLOW_PENYERAHAN->value  => ['flag' => 'PHARMACY_FLOW_DISPENSE', 'message' => 'Telah dilakukan penyerahan']
    ];
}
