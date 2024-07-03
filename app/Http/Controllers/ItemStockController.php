<?php

namespace App\Http\Controllers;

use App\Models\ItemStock;
use App\Http\Requests\StoreItemStockRequest;
use App\Http\Requests\UpdateItemStockRequest;
use App\Http\Resources\ItemStockResource;
use App\Http\Responses\APIResponse;
use App\Models\Item;
use App\Services\ItemStockService;
use Exception;

class ItemStockController extends Controller
{
    private $itemStockService;

    public function __construct(ItemStockService $itemStockService)
    {
        $this->itemStockService = $itemStockService;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreItemStockRequest  $request
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function store(StoreItemStockRequest $request, Item $item)
    {
        $param = $request->validated();
        try {
            $data = $this->itemStockService->createStock($item->id, $param['cogs'], $param['qty']);
            return APIResponse::success(new ItemStockResource($data), 'Item stock created successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error($ex->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ItemStock  $itemStock
     * @return \Illuminate\Http\Response
     */
    public function show(ItemStock $itemStock)
    {
        // Implement if needed
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateItemStockRequest  $request
     * @param  \App\Models\ItemStock  $itemStock
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateItemStockRequest $request, ItemStock $itemStock)
    {
        // Implement update logic if needed
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ItemStock  $itemStock
     * @return \Illuminate\Http\Response
     */
    public function destroy(ItemStock $itemStock)
    {
        // Implement delete logic if needed
    }
}
