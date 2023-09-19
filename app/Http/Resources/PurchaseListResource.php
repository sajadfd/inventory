<?php

namespace App\Http\Resources;

use App\Models\PurchaseItem;
use App\Models\PurchaseList;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseListResource extends JsonResource
{

    public function __construct(public $resource, private $withItems = true)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        /** @var PurchaseList $model */
        $model = $this->resource;

        $model->load('supplier');
        $model->loadBasicAttributes();
        if (!$this->withItems) {
            $model->makeHidden(['purchaseItems']);
            $model->append('initial_purchase_items');
        }


        $model->loadMissing(['supplier', 'bill', 'bill.payments', 'bill.billable', 'bill.billable.person']);
        $array = $model->toArray();
        if (isset($array['bill'])) {
            $array['bill'] = BillResource::make($model->bill);
        }
        if (isset($array['supplier'])) {
            $array['supplier'] = SupplierResource::make($model->supplier);
        }

        if (isset($array['purchase_items'])) {
            $array['purchase_items'] = $model->purchaseItems->transform(fn($i) => PurchaseItemResource::make($i))->toArray();
        }
        if (isset($array['initial_purchase_items'])) {
            $array['initial_purchase_items'] = $model->initial_purchase_items->transform(fn($i) => PurchaseItemResource::make($i))->toArray();
        }

        return $array;
    }
}
