<?php

namespace Hanafalah\ModulePharmacy\Contracts\Schemas;

use Illuminate\Database\Eloquent\Model;
use Hanafalah\LaravelSupport\Contracts\Supports\DataManagement;
use Hanafalah\ModulePharmacy\Contracts\Data\PharmacySaleData;

/**
 * @see \Hanafalah\ModulePharmacy\Schemas\PharmacySale
 * @method self setParamLogic(string $logic, bool $search_value = false, ?array $optionals = [])
 * @method self conditionals(mixed $conditionals)
 * @method bool deletePharmacySale()
 * @method bool prepareDeletePharmacySale(? array $attributes = null)
 * @method mixed getPharmacySale()
 * @method ?Model prepareShowPharmacySale(?Model $model = null, ?array $attributes = null)
 * @method array showPharmacySale(?Model $model = null)
 * @method Collection prepareViewPharmacySaleList()
 * @method array viewPharmacySaleList()
 * @method LengthAwarePaginator prepareViewPharmacySalePaginate(PaginateData $paginate_dto)
 * @method array viewPharmacySalePaginate(?PaginateData $paginate_dto = null)
 * @method array storePharmacySale(?PharmacySaleData $pharmacy_sale_dto = null)
 * @method Builder pharmacySale(mixed $conditionals = null)
 */
interface PharmacySale extends DataManagement
{
    public function prepareStorePharmacySale(PharmacySaleData $pharmacy_sale_dto): Model;
}
