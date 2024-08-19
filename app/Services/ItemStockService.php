<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemStock;
use App\Models\ItemStockFlow;

use App\Models\ItemStockOperation;
use Illuminate\Support\Facades\Cache;

class ItemStockService
{
    /**
     * Check if the total stock quantity of an item meets or exceeds the required amount.
     *
     * @param array $param Array containing 'item_id'.
     * @param int $stockNeeded Required stock quantity.
     * @return bool True if stock quantity meets or exceeds $stockNeeded, false otherwise.
     */
    public function checkStock($item_id, $stockNeeded)
    {
        $key = "ItemStock:checkStock:id[$item_id]";
        $data = Cache::remember($key, now()->addHours(1), function () use ($item_id, $key) {

            $listKey = "cache_key_list_check_stock_$item_id";
            $list = Cache::get($listKey, []);
            $list[] = $key;
            
            Cache::put($listKey, $list, 600);
            // Calculate the total stock quantity for the given item_id
            $totalStock = ItemStock::where('item_id', $item_id)->sum('qty');
            // Compare the total stock quantity with the required amount
            return $totalStock;
        });
        return $data >= $stockNeeded;;
    }
    /**
     * Create a new item stock record with the provided parameters.
     *
     * @param array $param Array containing parameters for creating item stock.
     * @return \App\Models\ItemStock Created ItemStock object.
     */
    public function updateStock($itemStock,$cogs, $val)
    {
        // Create a new item stock record using the provided parameters
        //$this->deductStock($itemStock, $itemStock->qty, "Updating process");
        //$this->addStock($itemStock, $val);
        return $itemStock;
    }
    /**
     * Create a new item stock record with the provided parameters.
     *
     * @param array $param Array containing parameters for creating item stock.
     * @return \App\Models\ItemStock Created ItemStock object.
     */
    public function createStock($item_id, $cogs, $qty)
    {

        // Create a new item stock record using the provided parameters
        $itemStock = ItemStock::create(['item_id' => $item_id, 'cogs' => $cogs]);

        $this->addStock($itemStock, $qty);
        return $itemStock;
    }

    /**
     * Add stock quantity to item stocks based on parameters.
     *
     * @param array $param Array containing 'item_id', 'cogs', and 'item_stock' (quantity to add).
     * @return void
     */
    public function rollback($itemStockUsage)
    {
        // rollback the quantity to item_stock
        $this->addStock($itemStockUsage->itemStock, $itemStockUsage->qty, $itemStockUsage->stock_flow, "Rollback added {$itemStockUsage->qty} units from item stock (ID: {$itemStockUsage->item_stock_id})");
    }

    /**
     * Add stock quantity to item stocks based on parameters.
     *
     * @param array $param Array containing 'item_id', 'cogs', and 'item_stock' (quantity to add).
     * @return void
     */
    public function addStock($itemStock, $value, $description = null)
    {
        // Increment the quantity if item stock exists
        ItemStockOperation::create([
            "item_id" => $itemStock->item_id,
            "type" => "add",
            "qty" => $value
        ])->stock_flow()->save(ItemStockFlow::create(["item_stock_id" => $itemStock->id, "qty" => $value, "type" => 2, "description" => $description ?? "add {$value} units from item stock (ID: {$itemStock->id})"]));
        $itemStock->increment('qty', $value);
    }

    /**
     * Deducts stock quantity from item stocks based on parameters.
     *
     * @param array $param Array containing 'item_stock' (item_id) and 'qty' (quantity to deduct).
     * @throws \RuntimeException If no item stocks found or insufficient stock to deduct.
     * @return void
     */
    public function deductStock($item_id, $qty, $source)
    {

        // Retrieve item stocks that match the item_id and have quantity greater than zero
        $itemStocks = ItemStock::where('item_id', $item_id)
            ->where('qty', '>', 0)
            ->get();

        // Iterate through each item stock and deduct the required quantity
        foreach ($itemStocks as $itemStock) {
            // Determine the amount to deduct, which is the lesser of $qty or the available quantity
            $deductValue = min($qty, $itemStock->qty);

            // Deduct the quantity from the item stock
            $itemStock->decrement('qty', $deductValue);
            $source->stock_flow()->save(ItemStockFlow::create(["item_stock_id" => $itemStock->id, "qty" => $deductValue, "type" => 2, "description" => "Deducted {$deductValue} units from item stock (ID: {$itemStock->id})"]));
            // Update the remaining quantity to deduct
            $qty -= $deductValue;

            // Exit the loop if $qty reaches zero
            if ($qty == 0) {
                break;
            }
        }
    }
    /**
     * Deducts stock quantity from item stocks based on parameters.
     *
     * @param array $param Array containing 'item_stock' (item_id) and 'qty' (quantity to deduct).
     * @throws \RuntimeException If no item stocks found or insufficient stock to deduct.
     * @return void
     */
    public function deductItemStock(ItemStock $itemStock, $deductValue, $source)
    {

        // Deduct the quantity from the item stock
        $itemStock->decrement('qty', $deductValue);
        Item::create(["item_stock_id" => $itemStock->id, "qty" => $deductValue, "type" => 2, "description" => "Deducted {$deductValue} units from item stock (ID: {$itemStock->id})"]);
    }
}
