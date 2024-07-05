<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Http\Resources\ItemStockResource;
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
        try {
            $perPage = $request->input('per_page', 10);
            $page = $request->input('page', 0);
            $data = $this->itemService->getData($request->all(), $page, $perPage);
            return APIResponse::success(ItemResource::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to create category. Please try again later.', 500);
        }
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
        $params = $request->validated();
        try {
            $data = $this->itemService->create($params);
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
        $params = $request->validated();
        try {
            $data = $this->itemService->update($item, $params);
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
    public function getItemStocks(Request $request, Item $item)
    {
        try {
            $data = $this->itemService->getItemStocks($item);
            return APIResponse::success(ItemStockResource::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to fetch data. Please try again later.', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/items/top-selling",
     *     operationId="getTopSellingItems",
     *     tags={"Items"},
     *     summary="Get top selling items",
     *     description="Returns a list of top selling items",
     *     @OA\Response(
     *         response=200,
     *         description="Fetch successfully",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Item")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to fetch data. Please try again later."
     *     ),
     *     @OA\Parameter(
     *         name="params",
     *         in="query",
     *         description="Parameters to filter the items",
     *         required=false,
     *         @OA\Schema(
     *             type="object"
     *         )
     *     ),
     *     security={
     *         {"api_key": {}}
     *     }
     * )
     * Retrieves the top-selling items from the inventory.
     * 
     * This function accepts a validated request, which includes any filters or parameters
     * needed to determine the top-selling items. It uses the itemService to query and
     * retrieve the data, then returns it in a formatted API response.
     *
     * @param Request $request The request object containing validated input parameters.
     * @return APIResponse Returns a successful API response with the collection of top-selling items
     *                     and a status message, or an error response if the process fails.
     */
    public function getTopSellingItems(Request $request)
    {
        $params = $request->validated();
        try {
            $data = $this->itemService->getTopSellingItems($params);
            return APIResponse::success(Item::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to fetch data. Please try again later.', 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/items/out-of-stock",
     *     summary="Fetch out of stock items",
     *     tags={"Items"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             default=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Item")
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Fetch successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to fetch data",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Failed to fetch data. Please try again later."
     *             )
     *         )
     *     )
     * )
 
     * Get out of stock items based on the provided request parameters.
     *
     * @param Request $request The HTTP request object containing input parameters.
     * @return \Illuminate\Http\JsonResponse JSON response with the list of out of stock items or error message.
     */
    public function getOutOfStockItems(Request $request)
    {
        $params = $request->validated();
        try {
            $data = $this->itemService->getOutOfStockItems($params);
            return APIResponse::success(Item::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return APIResponse::error('Failed to fetch data. Please try again later.', 500);
        }
    }
}
