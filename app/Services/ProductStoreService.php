<?php

namespace App\Services;

use App\Contracts\ProductItemInterface;
use App\Enums\ProductTransactionEnum;
use App\Enums\ProductUnitType;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\PurchaseItem;
use Exception;

class ProductStoreService
{

    public static function convertCountToUnit(float|int $count, ?ProductUnit $fromUnit = null, ?ProductUnit $toUnit = null): float
    {
        $properCount = $count;
        if ($fromUnit) {
            $properCount = match ($fromUnit->type) {
                ProductUnitType::smaller => $count / $fromUnit->factor,
                ProductUnitType::larger => $count * $fromUnit->factor,
            };
        }
        if ($toUnit) {
            $properCount = match ($toUnit->type) {
                ProductUnitType::smaller => $count * $toUnit->factor,
                ProductUnitType::larger => $count / $toUnit->factor,
            };
        }
        return $properCount;
    }

    /**
     * @throws Exception
     */
    public static function UpdateStoreFromPurchase(Product $product, PurchaseItem $purchaseItem, ProductUnit $productUnit = null): bool
    {
        if ($purchaseItem->count <= 0) {
            throw new Exception(__('updated_count_must_be_greater_than_0'));
        }

        $product->transactions()->create([
            'count' => $purchaseItem->count,
            'type' => ProductTransactionEnum::Purchase,
            'targetable_id' => $purchaseItem->id,
            'targetable_type' => get_class($purchaseItem),
            'target_product_unit_id' => $productUnit?->id,
        ]);
        $product->increment('store', self::convertCountToUnit($purchaseItem->count, $productUnit));
        return true;
    }

    /**
     * @throws Exception
     */
    public static function RefundPurchaseFromStore(Product $product, PurchaseItem $purchaseItem): bool
    {
        if ($purchaseItem->usageTransactions->isNotEmpty() || $purchaseItem->used > 0) {
            throw new Exception(__('Purchase item has uses, it is improper to delete'));
        }
        $purchaseItem->purchaseTransaction->delete();
        $product->decrement('store', $purchaseItem->count);
        return true;
    }

    /**
     * @throws Exception
     */
    public static function UtilizeStoreInSale(Product $product, $cnt, ProductItemInterface $productItem, ?ProductUnit $saleProductUnit): void
    {
        if ($cnt <= 0) {
            throw new Exception("Utilized count must be greater than 0.");
        }
        $hasInitialStore = $product->initialStore?->in_stock_count > 0;
        $inStockPurchaseItems = $product->inStockPurchaseItems()->get();
        $neededCountInSaleUnit = $cnt;
        $trials = 0;

        while ($neededCountInSaleUnit > 0) {
            $trials++;
            if ($hasInitialStore && $hasInitialStore = $product->initialStore->in_stock_count > 0) {
                $sourceItem = $product->initialStore;
            } else {
                $sourceItem = $inStockPurchaseItems->shift();
            }
            if (!$sourceItem) {
                throw new Exception("Product has no available sources");
            }

            $neededCountInSourceUnit = self::convertCountToUnit($neededCountInSaleUnit, $saleProductUnit, $sourceItem->productUnit);

            $usingCountInSourceUnit = min($neededCountInSourceUnit, $sourceItem->in_stock_count);
            $usingCountInSaleUnit = self::convertCountToUnit($usingCountInSourceUnit, $sourceItem->productUnit, $saleProductUnit);

            $sourceItem->increment('used', $usingCountInSourceUnit);

            $neededCountInSaleUnit -= $usingCountInSaleUnit;

            $product->transactions()->create([
                'count' => $usingCountInSaleUnit,
                'type' => ProductTransactionEnum::Sale,
                'sourceable_id' => $sourceItem->id,
                'sourceable_type' => get_class($sourceItem),
                'targetable_id' => $productItem->id,
                'targetable_type' => get_class($productItem),
                'source_product_unit_id' => $sourceItem->productUnit?->id,
                'target_product_unit_id' => $saleProductUnit?->id,
            ]);
        }

        $product->decrement('store', self::convertCountToUnit($cnt, $saleProductUnit));
    }

    /**
     * @throws Exception
     */
    public static function RefundSaleIntoStore(Product $product, $cnt, ProductItemInterface $productItem, ?ProductUnit $saleProductUnit): void
    {
        if ($cnt >= 0) {
            throw new Exception("Refunded count must be less than 0.");
        }
        $cnt = abs($cnt);

        $transactions = $productItem->transactions()->orderByDesc('created_at')->orderByDesc('id')->get();
        $neededCountInSaleUnit = $cnt;
        while ($neededCountInSaleUnit > 0) {
            $productTransaction = $transactions->shift();

            $sourceItem = $productTransaction->sourceable;

            if ($productTransaction->count <= $neededCountInSaleUnit) {
                $refundedCountInSaleUnit = $productTransaction->count;
                $refundedCountInSourceUnit = self::convertCountToUnit($refundedCountInSaleUnit, $saleProductUnit, $sourceItem->productUnit);
                $sourceItem->decrement('used', $refundedCountInSourceUnit);
                $productTransaction->delete();
            } else {
                $refundedCountInSaleUnit = $neededCountInSaleUnit;
                $refundedCountInSourceUnit = self::convertCountToUnit($refundedCountInSaleUnit, $saleProductUnit, $sourceItem->productUnit);
                $sourceItem->decrement('used', $refundedCountInSourceUnit);
                $productTransaction->decrement('count', $refundedCountInSaleUnit);
            }
            $neededCountInSaleUnit -= $refundedCountInSaleUnit;
        }

        $product->increment('store', self::convertCountToUnit($cnt, $saleProductUnit));
    }
}
