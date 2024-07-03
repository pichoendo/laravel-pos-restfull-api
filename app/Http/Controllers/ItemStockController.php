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
     * @OA\Post(
     *     path="/api/items/{item}/stocks",
     *     summary="Create item stock",
     *     description="Create a new item stock",
     *     operationId="storeItemStock",
     *     tags={"ItemStocks"},
     *     @OA\Parameter(
     *         name="item",
     *         in="path",
     *         description="Item ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreItemStockRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item stock created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/ItemStock"),
     *             @OA\Property(property="message", type="string", example="Item stock created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to create item stock. Please try again later.")
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/item-stocks/{itemStock}",
     *     summary="Get item stock",
     *     description="Fetch a specific item stock by ID",
     *     operationId="showItemStock",
     *     tags={"ItemStocks"},
     *     @OA\Parameter(
     *         name="itemStock",
     *         in="path",
     *         description="Item Stock ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item stock fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/ItemStock"),
     *             @OA\Property(property="message", type="string", example="Item stock fetched successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to fetch item stock. Please try again later.")
     *         )
     *     )
     * )
     */
    public function show(ItemStock $itemStock)
    {
        // Implement if needed
    }

    /**
     * @OA\Put(
     *     path="/api/item-stocks/{itemStock}",
     *     summary="Update item stock",
     *     description="Update an existing item stock",
     *     operationId="updateItemStock",
     *     tags={"ItemStocks"},
     *     @OA\Parameter(
     *         name="itemStock",
     *         in="path",
     *         description="Item Stock ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateItemStockRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item stock updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/ItemStock"),
     *             @OA\Property(property="message", type="string", example="Item stock updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to update item stock. Please try again later.")
     *         )
     *     )
     * )
     */
    public function update(UpdateItemStockRequest $request, ItemStock $itemStock)
    {
        // Implement update logic if needed
    }

    /**
     * @OA\Delete(
     *     path="/api/item-stocks/{itemStock}",
     *     summary="Delete item stock",
     *     description="Delete a specific item stock by ID",
     *     operationId="deleteItemStock",
     *     tags={"ItemStocks"},
     *     @OA\Parameter(
     *         name="itemStock",
     *         in="path",
     *         description="Item Stock ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item stock deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Item stock deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to delete item stock. Please try again later.")
     *         )
     *     )
     * )
     */
    public function destroy(ItemStock $itemStock)
    {
        // Implement delete logic if needed
    }
}
