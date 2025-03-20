<?php

declare(strict_types=1);

namespace Hanafalah\ModulePharmacy;

use Hanafalah\LaravelSupport\Providers\BaseServiceProvider;

class ModulePharmacyServiceProvider extends BaseServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return $this
     */
    public function register()
    {
        $this->registerMainClass(ModulePharmacy::class)
            ->registerCommandService(Providers\CommandServiceProvider::class)
            ->registers([
                '*',
                'Services' => function () {
                    $this->binds([
                        Contracts\ModulePharmacy::class                  => ModulePharmacy::class,
                        Contracts\PharmacySale::class                    => Schemas\PharmacySale::class,
                        Contracts\PharmacySaleExamination::class         => Schemas\Dispense\PharmacySaleExamination::class,
                        Contracts\PharmacySaleVisitRegistration::class   => Schemas\PharmacySaleVisitRegistration::class,
                        Contracts\PharmacyExamination::class             => Schemas\PharmacyExamination::class
                    ]);
                }
            ]);
        $this->setupExaminationLists();
    }

    private function setupExaminationLists(): self
    {
        $examination_lists = config('database.examinations', []);
        $lists = config('module-pharmacy.examinations', []);
        $examination_lists = array_merge($examination_lists, $lists);
        config(['database.examinations' => $examination_lists]);
        return $this;
    }

    /**
     * Get the base path of the package.
     *
     * @return string
     */
    protected function dir(): string
    {
        return __DIR__ . '/';
    }

    protected function migrationPath(string $path = ''): string
    {
        return database_path($path);
    }
}
