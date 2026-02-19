# CLAUDE.md - Module Pharmacy

## Overview

`hanafalah/module-pharmacy` is a Laravel package that provides pharmacy management functionality for the Wellmed healthcare system. It handles pharmacy sales (direct purchases without prescription), dispense workflows for prescription medications, and pharmacy examination processes.

**Namespace:** `Hanafalah\ModulePharmacy\`

## Dependencies

```json
{
    "hanafalah/laravel-support": "dev-main",
    "hanafalah/module-warehouse": "dev-main",
    "hanafalah/module-transaction": "dev-main",
    "hanafalah/module-patient": "dev-main",
    "hanafalah/module-payment": "dev-main"
}
```

## Directory Structure

```
src/
├── Commands/                    # Artisan commands
│   ├── InstallMakeCommand.php
│   └── EnvironmentCommand.php
├── Contracts/                   # Interfaces
│   ├── ModulePharmacy.php
│   ├── Data/                    # Data Transfer Object contracts
│   │   ├── DispenseExaminationData.php
│   │   ├── PharmacyExaminationData.php
│   │   ├── PharmacySaleData.php
│   │   └── PharmacySaleVisitRegistrationData.php
│   └── Schemas/                 # Schema contracts
│       ├── DispenseExamination.php
│       ├── PharmacyExamination.php
│       ├── PharmacySale.php
│       └── PharmacySaleVisitRegistration.php
├── Data/                        # DTOs (Spatie Laravel Data)
│   ├── DispenseExaminationData.php
│   ├── PharmacyExaminationData.php
│   ├── PharmacySaleData.php
│   └── PharmacySaleVisitRegistrationData.php
├── Enums/                       # Enum definitions
│   ├── PharmacySale/
│   │   ├── Activity.php
│   │   ├── ActivityStatus.php
│   │   └── Status.php
│   └── PharmacySaleVisitRegistration/
│       ├── Activity.php
│       └── ActivityStatus.php
├── Models/                      # Eloquent models
│   └── PharmacySale/
│       ├── PharmacySale.php
│       ├── PharmacySaleVisitRegistration.php
│       └── Dispense/
│           └── PharmacySaleExamination.php
├── Providers/
│   └── CommandServiceProvider.php
├── Resources/                   # API Resources
│   ├── PharmacySale/
│   │   ├── ShowPharmacySale.php
│   │   └── ViewPharmacySale.php
│   └── PharmacySaleVisitRegistration/
│       ├── ShowPharmacySaleVisitRegistration.php
│       └── ViewPharmacySaleVisitRegistration.php
├── Schemas/                     # Business logic schemas
│   ├── DispenseExamination.php
│   ├── PharmacyExamination.php
│   ├── PharmacySale.php
│   └── PharmacySaleVisitRegistration.php
├── ModulePharmacy.php           # Main package class
└── ModulePharmacyServiceProvider.php
```

## Key Models

### PharmacySale

Extends `VisitPatient` - represents a pharmacy sale visit (direct purchase or prescription fulfillment).

- **Table:** `visit_patients`
- **Flag:** `PharmacySale`
- **Key attributes:**
  - `name`, `nik`, `dob`, `medical_record` (patient info from props)
  - `consument_name`, `consument_phone` (buyer info)
  - `queue_number`, `flag`, `status`

### PharmacySaleVisitRegistration

Extends `VisitRegistration` - represents the registration step for a pharmacy visit.

- **Table:** `visit_registrations`
- **Relationships:** `medicService`, `patientType`, `visitExamination`, `visitPatient`

### PharmacySaleExamination

Extends `Assessment` - represents dispense examination/assessment data.

- **Table:** `assessments`
- **Specific fields:** `consument`, `dispense`

## Workflow States

### PharmacySale Activity States

```php
// Activity
PHARMACY_SALE_VISIT = 'pharmacy_sale_visit'

// Activity Statuses
PHARMACY_SALE_VISIT_DRAFT     = 1  // "Antrian peresepan"
PHARMACY_SALE_VISIT_PROCESSED = 2  // "Kunjungan dilakukan"
PHARMACY_SALE_VISIT_FINISHED  = 3  // "Kunjungan selesai"
PHARMACY_SALE_VISIT_CANCELLED = 4  // "Kunjungan dibatalkan"
```

### PharmacySaleVisitRegistration Activity States (Pharmacy Flow)

```php
// Activity
PHARMACY_FLOW = 'pharmacy_flow'

// Activity Statuses
PHARMACY_FLOW_QUEUE      = 1  // "Dalam antrian kefarmasian"
PHARMACY_FLOW_FRONTLINE  = 2  // "Masuk tahap frontline"
PHARMACY_FLOW_DISPENSE   = 3  // "Dilakukan dispense"
PHARMACY_FLOW_PENYERAHAN = 4  // "Telah dilakukan penyerahan" (handover completed)
```

## Schemas (Business Logic)

### PharmacySale Schema

- `prepareStorePharmacySale(PharmacySaleData $dto)` - Creates a pharmacy sale visit

### PharmacySaleVisitRegistration Schema

- `prepareStorePharmacySaleVisitRegistration(?array $attributes)` - Creates visit registration
- `toFrontline()` - Moves registration to frontline stage

### PharmacyExamination Schema

- `commitExamination(ExaminationData $dto)` - Commits examination and updates stock
- `toComplete()` - Marks pharmacy flow as complete (penyerahan)

### DispenseExamination Schema

- `toDispense(DispenseExaminationData $dto)` - Handles dispense workflow

## Contract Bindings

The ServiceProvider binds the following contracts:

```php
Contracts\ModulePharmacy::class              => ModulePharmacy::class
Contracts\PharmacySale::class                => Schemas\PharmacySale::class
Contracts\PharmacySaleExamination::class     => Schemas\Dispense\PharmacySaleExamination::class
Contracts\PharmacySaleVisitRegistration::class => Schemas\PharmacySaleVisitRegistration::class
Contracts\PharmacyExamination::class         => Schemas\PharmacyExamination::class
```

## Usage Patterns

### Creating a Pharmacy Sale

```php
// Using the schema
$schema = app(Contracts\Schemas\PharmacySale::class);
$pharmacySale = $schema->prepareStorePharmacySale($pharmacySaleData);

// Or via request DTO
$result = $schema->storePharmacySale();
```

### Creating a Visit Registration

```php
$schema = app(Contracts\Schemas\PharmacySaleVisitRegistration::class);
$registration = $schema->prepareStorePharmacySaleVisitRegistration([
    'patient_id' => $patientId,
    'consument' => [
        'name' => 'John Doe',
        'phone' => '08123456789'
    ]
]);
```

### Processing from Prescription (VisitExamination reference)

The `PharmacySaleData` DTO supports creating pharmacy sales from existing prescriptions:

```php
$data = [
    'reference_type' => 'VisitExamination',
    'visit_examination_id' => $visitExaminationId
];
// This will automatically copy prescription data from the source examination
```

## Configuration

The module merges examination lists into database config:

```php
// In config/module-pharmacy.php
return [
    'examinations' => [
        // Examination type configurations
    ]
];
```

## IMPORTANT: ServiceProvider Warning

**DO NOT use `registers(['*'])` in BaseServiceProvider extensions.**

The current implementation uses wildcard registration which can cause performance issues and unexpected behavior:

```php
// CURRENT (problematic)
$this->registers([
    '*',  // <-- This wildcard registration should be avoided
    'Services' => function () { ... }
]);
```

**Recommended approach:**

```php
// BETTER - explicitly register only what you need
$this->registerMainClass(ModulePharmacy::class)
    ->registerCommandService(Providers\CommandServiceProvider::class)
    ->registers([
        'Services' => function () {
            $this->binds([
                // explicit bindings
            ]);
        }
    ]);
```

The wildcard `'*'` causes the BaseServiceProvider to auto-discover and register all files, which:
1. Slows down application boot time
2. May register classes that should not be auto-registered
3. Can cause conflicts with other packages
4. Makes debugging registration issues difficult

## Related Modules

- `module-patient` - Patient management, visit patients, visit registrations
- `module-warehouse` - Stock management for medications
- `module-transaction` - Transaction handling
- `module-payment` - Payment processing
- `module-examination` - Examination and assessment base classes
- `module-medic-service` - Medical service (INSTALASI FARMASI label)
