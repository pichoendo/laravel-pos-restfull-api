<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Sales;
use App\Models\SalesItem;
use App\Models\SalesPaymentWithCard;
use App\Models\SalesWithCoupon;
use App\Notifications\SalesReport;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SalesService
{


    /**
     * SalesService constructor.
     *
     * @param MemberSalesPointLogService $memberSalesPointLogService
     * @param EmployeeComissionLogService $employeeComissionLogService
     * @param ItemStockService $itemStockService
     */
    public function __construct(
        public MemberSalesPointLogService $memberSalesPointLogService,
        public EmployeeComissionLogService $employeeComissionLogService,
        public ItemStockService $itemStockService
    ) {}

    /**
     * Create a new sales record.
     *
     * @param array $param
     * @return Sales
     */
    public function create(array $param): Sales
    {
        return DB::transaction(function () use ($param) {
            // Create a new sales record
            $sales = Sales::create($param);

            // Apply discount if a coupon is provided
            if (isset($param['coupon_id'])) {
                $this->applyDiscount($sales, $param['coupon_id']);
            }

            // Process each sales item in the cart
            $this->processSalesItems($sales, $param);

            // Calculate the total sales
            $this->calculateSales($sales);

            // Perform additional processing if the sales status is 'success'
            if ($sales->status == 'success') {
                $this->processAfterSales($sales, $param['card_no'] ?? null);
            }

            return $sales;
        });
    }

    /**
     * Process sales items.
     *
     * @param Sales $sales
     * @param array $param
     * @param bool $update
     * @return void
     */
    private function processSalesItems(Sales $sales, array $params, bool $update = false): void
    {
        // Get existing sales items if updating
        $existingSalesItems = $update ? $sales->items->keyBy('item_id') : [];

        // Iterate through each item in the cart
        foreach ($params['cart'] as $item_cart) {
            $itemId = $item_cart['item_id'];
            $quantity = $item_cart['qty'];
            $item = Item::find($itemId);
            // Check if there is enough stock for the item
            if ($this->itemStockService->checkStock($itemId, $quantity)) {
                $salesItem = $update ? $existingSalesItems->get($itemId) : false;
                if ($salesItem) {
                    // Calculate the quantity difference
                    $quantityDifference = $quantity - $salesItem->qty;

                    if ($quantityDifference > 0) {
                        // Deduct stock for the difference
                        $this->itemStockService->deductStock($itemId, $quantityDifference, $salesItem);
                    } else {
                        // Add stock for the difference
                        $this->itemStockService->addStock($salesItem->stock_flow->itemStock, abs($quantityDifference));
                    }
                    // Update the sales item
                    $salesItem->update($item_cart);
                } else {
                    // Create a new sales item
                    $salesItem = SalesItem::create($item_cart);
                    $sales->items()->save($salesItem);
                    // Deduct stock for the new sales item
                    $this->itemStockService->deductStock($itemId, $quantity, $salesItem);
                }
            } else {
                throw new Exception("Stock of $item->name is not enough only($item->stock_count)");
            }
        }
    }

    /**
     * Retrieve paginated sales data based on search parameters.
     *
     * @param array $param Array containing search parameters, e.g., ['search' => 'keyword'].
     * @param int $page Page number for pagination.
     * @param int $perPage Number of items per page for pagination.
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getData($search, $page, $perPage): LengthAwarePaginator
    {
        // Generate a unique cache key based on page, perPage, and search parameters
        $key = "sales:page[$page]:perPage[$perPage]:search[$search]";

        // Retrieve data from cache or execute the query and store the result
        $data = Cache::remember($key, now()->addDay(1), function () use ($search, $key, $perPage) {
            $query = Sales::query();

            // Maintain a list of cache keys for easy invalidation later
            $listKey = "cache_key_list_sales";
            $list = Cache::get($listKey, []);

            if (!in_array($key, $list)) {
                $list[] = $key;
                Cache::put($listKey, $list, 600); // Store the list for 10 minutes
            }

            // Apply search filter if search parameter is provided
            if (!empty($search)) {
                $query->where('name', 'LIKE', "%{$search}%");
            }

            // Return paginated results
            return $query->paginate($perPage);
        });

        return $data;
    }

    /**
     * Apply discount to the sales using a coupon.
     *
     * @param Sales $sales
     * @param int $coupon_id
     * @return void
     */
    private function applyDiscount(Sales $sales, int $coupon_id): void
    {
        if ($sales->coupon) {
            // Create the SalesWithCoupon entry and associate it with the sales transaction
            $salesWithCoupon = SalesWithCoupon::create(['coupon_id' => $sales->coupon->id]);
            $sales->payWithCoupon()->save($salesWithCoupon);

            // Retrieve the coupon's value
            $couponValue = $sales->coupon->value;

            // Update the discount on the sale if it's different from the coupon value
            if ($sales->discount !== $couponValue) {
                $sales->update(['discount' => $couponValue]);
            }
        }
    }

    /**
     * Process additional steps after a sale is successful.
     *
     * @param Sales $sales
     * @param string|null $card_no
     * @return void
     */
    private function processAfterSales(Sales $sales, ?string $card_no = null): void
    {
        // Add points to the member if applicable
        if ($sales->member) {
            $this->memberSalesPointLogService->addPoint($sales);
            $sales->member->notify(new SalesReport($sales));
        }

        // Process payment with card if card number is provided
        if ($card_no) {
            $sales->payWithCard()->save(SalesPaymentWithCard::create(['card_no' => $card_no]));
        }

        // Add commission for the employee
        $this->employeeComissionLogService->addCommission($sales);
    }

    /**
     * Update an existing sales record.
     *
     * @param Sales $sales
     * @param array $param
     * @return Sales
     */
    public function update(Sales $sales, array $param): Sales
    {
        return DB::transaction(function () use ($sales, $param) {
            $oldStatus = $sales->status;

            if ($oldStatus == 'hold') {
                $sales->update($param);
                if ($sales->status === 'success') {
                    $itemOnNewCart = array_column($param['cart'], 'item_id');

                    // Delete non-existing items and rollback stock
                    $this->deleteUnexistingItemsAndRollbackStock($sales, $itemOnNewCart);
                    // Process sales items
                    $this->processSalesItems($sales, $param, true);
                    // Calculate sales totals
                    $this->calculateSales($sales);
                    // Process additional steps after sales
                    $this->processAfterSales($sales, $param['card_no'] ?? null);
                } else {
                    foreach ($sales->items as $item) {
                        $this->itemStockService->rollback($item->stock_flow);
                    }
                }
            }
    
            return Sales::with(['items'])->find($sales->id);
        });
    }

    /**
     * Delete items that no longer exist in the cart and rollback their stock.
     *
     * @param Sales $sales
     * @param array $itemOnNewCart
     * @return void
     */
    private function deleteUnexistingItemsAndRollbackStock(Sales $sales, array $itemOnNewCart): void
    {
        $itemsToDelete = $sales->items()->whereNotIn('item_id', $itemOnNewCart)->with(['operation'])->get();
        foreach ($itemsToDelete as $item) {
            $this->itemStockService->addStock($item->operation, $item->qty, 'canceled item');
            $item->delete();
        }
    }

    /**
     * Delete a sales record.
     *
     * @param Sales $sales
     * @return bool|null
     * @throws \Exception
     */
    public function destroy(Sales $sales)
    {
        return $sales->delete();
    }

    /**
     * Calculate the total sales amounts and update the sales record.
     *
     * @param Sales $sales
     * @return void
     */
    public function calculateSales(Sales $sales): void
    {
        $sub_total = $sales->items()->sum('sub_total') - $sales->discount;
        $sales->update([
            'sub_total' => $sub_total,
            'tax' => round($sub_total * 0.1),
            'total' => round($sub_total * 1.1)
        ]);
    }
}
