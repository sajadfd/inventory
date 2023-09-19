<?php

namespace App\Http\Resources;

use App\Enums\SaleType;
use App\Models\SaleItem;
use App\Models\SaleList;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleListResource extends JsonResource
{


    public function __construct(public $resource, private $withItems = true)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        /** @var SaleList $model */
        $model = $this->resource;
        $model->loadBasicAttributes();
        if (!$this->withItems) {
            $model->makeHidden(['saleItems']);
            $model->append('initial_sale_items');
        }

        $model->loadMissing(['customer', 'bill', 'bill.payments', 'bill.billable', 'bill.billable.person']);
        if ($model->type === SaleType::InventorySale) {
            $model->loadMissing(['mechanic','car', 'car.carType', 'car.carModel', 'car.color', 'car.carType', 'diagnosis']);
        }

        $array = $model->toArray();
        if (isset($array['car'])) {
            $array['car'] = CarResource::make($model->car);
        }
        if (isset($array['bill'])) {
            $array['bill'] = BillResource::make($model->bill);
        }
        if (isset($array['customer'])) {
            $array['customer'] = CustomerResource::make($model->customer);
        }
        if (isset($array['mechanic'])) {
            $array['mechanic'] = MechanicResource::make($model->mechanic);
        }

        if (isset($array['sale_items'])) {
            $array['sale_items'] = $model->saleItems->transform(fn($i) => SaleItemResource::make($i))->toArray();
        }
        if (isset($array['initial_sale_items'])) {
            $array['initial_sale_items'] = $model->initial_sale_items->transform(fn($i) => SaleItemResource::make($i))->toArray();
        }

        return $array;
    }
}
