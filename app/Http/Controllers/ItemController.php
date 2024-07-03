<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Http\Responses\APIResponse;
use App\Services\ItemService;
use Exception;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    private ItemService $itemService;

    /**
     * ItemController constructor.
     *
     * @param ItemService $itemService
     */
    public function __construct(ItemService $itemService)
    {
        $this->itemService = $itemService;
    }

    /**
     * @OA\Get(
     *     path="/api/items",
     *     summary="Get items",
     *     description="Fetch a list of items with optional search query",
     *     operationId="getItems",
     *     tags={"Items"},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Items fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Item")),
     *             @OA\Property(property="message", type="string", example="Fetch successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to fetch items. Please try again later.")
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = Item::query();
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $query = $query->paginate($perPage);

        return APIResponse::success(ItemResource::collection($query), 'Fetch successfully', 200);
    }

    /**
     * @OA\Post(
     *     path="/api/items",
     *     summary="Create item",
     *     description="Create a new item",
     *     operationId="storeItem",
     *     tags={"Items"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/StoreItemRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Item"),
     *             @OA\Property(property="message", type="string", example="Item created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to create item. Please try again later.")
     *         )
     *     )
     * )
     */
    public function store(StoreItemRequest $request)
    {
        $param = $request->validated();
        try {
            $data = $this->itemService->create($param);
            return APIResponse::success(new ItemResource($data), 'Item created successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to create item. Please try again later.', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/items/{item}",
     *     summary="Get item",
     *     description="Fetch a specific item by ID",
     *     operationId="getItem",
     *     tags={"Items"},
     *     @OA\Parameter(
     *         name="item",
     *         in="path",
     *         description="Item ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Item"),
     *             @OA\Property(property="message", type="string", example="Fetch successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to fetch item. Please try again later.")
     *         )
     *     )
     * )
     */
    public function show(Item $item)
    {
        return APIResponse::success(new ItemResource($item), 'Fetch successfully', 200);
    }

    /**
     * @OA\Put(
     *     path="/api/items/{item}",
     *     summary="Update item",
     *     description="Update an existing item",
     *     operationId="updateItem",
     *     tags={"Items"},
     *     @OA\Parameter(
     *         name="item",
     *         in="path",
     *         description="Item ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateItemRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Item"),
     *             @OA\Property(property="message", type="string", example="Item updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to update item. Please try again later.")
     *         )
     *     )
     * )
     */
    public function update(UpdateItemRequest $request, Item $item)
    {
        $param = $request->validated();
        try {
            $data = $this->itemService->update($item, $param);
            return APIResponse::success(new ItemResource($data), 'Item updated successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to update item. Please try again later.', 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/items/{item}",
     *     summary="Delete item",
     *     description="Delete a specific item by ID",
     *     operationId="deleteItem",
     *     tags={"Items"},
     *     @OA\Parameter(
     *         name="item",
     *         in="path",
     *         description="Item ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Item deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Failed to delete item. Please try again later.")
     *         )
     *     )
     * )
     */
    public function destroy(Item $item)
    {
        try {
            $this->itemService->destroy($item);
            return APIResponse::success(null, 'Deleted successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to delete item. Please try again later.', 500);
        }
    }
}
