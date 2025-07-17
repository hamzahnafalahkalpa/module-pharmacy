<?php

namespace Hanafalah\ModulePharmacy\Models\PharmacySale;

use Hanafalah\LaravelSupport\Concerns\Support\HasActivity;
use Hanafalah\ModulePatient\Models\EMR\VisitPatient;
use Hanafalah\ModulePharmacy\Enums\PharmacySale\{
    Activity,
    ActivityStatus,
    Status
};
use Hanafalah\ModulePharmacy\Resources\PharmacySale\{ShowPharmacySale,ViewPharmacySale};

class PharmacySale extends VisitPatient
{
    use HasActivity;
    protected $table = 'visit_patients';

    const PHARMACY_SALE_VISIT = 'PHARMACY_VISIT';
    const CLINICAL_VISIT      = 'CLINICAL_VISIT';

    public static $flag = 'PHARMACY_VISIT';

    protected $casts = [
        'name'            => 'string',
        'consument_name'  => 'string',
        'consument_phone' => 'string',
        'queue_number'    => 'string',
        'created_at'      => 'date',
        'nik'             => 'string',
        'dob'             => 'immutable_date',
        'medical_record'  => 'string'
    ];

    public function getPropsQuery(): array
    {
        return [
            'name'            => 'props->prop_patient->prop_people->name',
            'dob'             => 'props->prop_patient->prop_people->dob',
            'nik'             => 'props->prop_patient->nik',
            'medical_record'  => 'props->prop_patient->medical_record',
            'consument_name'  => 'props->prop_consument->name',
            'consument_phone' => 'props->prop_consument->phone'
        ];
    }

    protected static function booted(): void
    {
        parent::booted();
        static::addGlobalScope('flag', function ($query) {
            $query->flagIn(self::PHARMACY_SALE_VISIT);
        });
        static::creating(function ($query) {
            $query->visit_code ??= static::hasEncoding('PHARMACY_SALE');
            $query->status     ??= Status::PENDING->value;
            $query->visited_at ??= now();
            $query->flag         = self::PHARMACY_SALE_VISIT;
        });
    }

    public function getViewResource(){return ViewPharmacySale::class;}
    public function getShowResource(){return ShowPharmacySale::class;}
    public array $activityList = [
        Activity::PHARMACY_SALE_VISIT->value . '_' . ActivityStatus::PHARMACY_SALE_VISIT_DRAFT->value     => ['flag' => 'PHARMACY_SALE_VISIT_DRAFT', 'message' => 'Antrian peresepan'],
        Activity::PHARMACY_SALE_VISIT->value . '_' . ActivityStatus::PHARMACY_SALE_VISIT_PROCESSED->value  => ['flag' => 'PHARMACY_SALE_VISIT_PROCESSED', 'message' => 'Kunjungan dilakukan'],
        Activity::PHARMACY_SALE_VISIT->value . '_' . ActivityStatus::PHARMACY_SALE_VISIT_FINISHED->value  => ['flag' => 'PHARMACY_SALE_VISIT_FINISHED', 'message' => 'Kunjungan selesai'],
        Activity::PHARMACY_SALE_VISIT->value . '_' . ActivityStatus::PHARMACY_SALE_VISIT_CANCELLED->value => ['flag' => 'PHARMACY_SALE_VISIT_CANCELLED', 'message' => 'Kunjungan dibatalkan'],
    ];
}
