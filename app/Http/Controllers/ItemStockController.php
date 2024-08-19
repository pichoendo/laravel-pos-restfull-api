<?php

namespace App\Http\Controllers;

use App\Models\ItemStock;
use App\Http\Requests\StoreItemStockRequest;
use App\Http\Requests\UpdateItemStockRequest;
use App\Http\Resources\ItemStockResource;
use App\Models\Item;
use App\Responses\ApiResponse;
use App\Services\ItemStockService;
use Exception;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ItemStockController extends Controller implements HasMiddleware
{
    /**
     * ItemStockController constructor.
     *
     * 
     * This constructor initializes the `ItemStockController` by injecting 
     * the `ItemStockService` and `ApiResponse` dependencies. The `ItemStockService` 
     * is used for handling ItemStock CRUD logic, and the `ApiResponse` 
     * is passed to the parent controller to handle standardized API responses.
     *
     * 
     * @param ItemService $itemService
     * @param  \App\Http\Responses\ApiResponse  $apiResponse  The service for standardized API responses.
     */
    public function __construct(public ItemStockService $itemStockService, ApiResponse $apiResponse)
    {
        parent::__construct($apiResponse);
    }

    /**
     * Define the middleware applied to the controller methods.
     *
     * This method returns an array of middleware configurations:
     * - The 'manage_item_stock' permission is required to access the 'update', 'store', and 'destroy' methods.
     * - The 'manage_item_stock' or 'consume_item_stock' permissions are required to access the 'index', 'show', and 'getCategoryListOfItems' methods.
     *
     * @return array The array of middleware applied to the controller methods.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_item_stock', only: ['update',  'store', 'destroy']), // apply for manage_item_stock
            new Middleware('permission:manage_item_stock|consume_item_stock', only: ['index', 'show', 'getCategoryListOfItems',]), // apply for manage_item_stock & consume_item_stock
        ];
    }


    /**
     * Display the specified item stock .
     *
     * @OA\Get(
     *     path="/api/v1/item/stock/{uuid}",
     *     summary="Retrieve a item stock by its UUID.",
     *     description="This endpoint allows you to retrieve details of a specific item stock using its UUID. Provide the UUID of the item in the request to get information such as the item's name, description, and any other relevant details",
     *     operationId="GetItemStock",
     *     tags={"Item Stock"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer <your-token-here>"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the data",
     *         @OA\Schema(
     *             type="string",
     *             example="Skfr-4584kndir4-456"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the data retrived successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data item fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                     @OA\Property(property="id", type="string", example="1"),
     *                     @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                     @OA\Property(property="cogs", type="string", example="2500"),
     *                     @OA\Property(property="qty", type="string", example="34")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Response when the session has expired or the user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=401),
     *             @OA\Property(property="message", type="string", example="Your session has expired or you are not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Response when the data was not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=404),
     *             @OA\Property(property="message", type="string", example="The data was not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Response when there are issues on the server",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * 
     * @param \App\Models\Coupon $coupon The coupon instance to be displayed.
     * @return \Illuminate\Http\Response The response containing the coupon details.
     */
    public function show(ItemStock $stock)
    {
        return $this->apiResponse->success(new ItemStockResource($stock), 'Fetch successful', 200);
    }


    /**
     * Retrieve a paginated list of StockItem.
     *
     * @OA\Post(
     *     path="/api/v1/item/{item-uuid}/stock",
     *     summary="Save a item stock",
     *     description="The endpoint for create a item stock.",
     *     operationId="SaveItemStock",
     *     tags={"Item Stock"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer <your-token-here>"
     *         )
     *     ),
     *      @OA\Parameter(
     *         name="item-uuid",
     *         in="path",
     *         required=true,
     *         description="The UUID of the item",
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cogs","qty"},
     *             @OA\Property(property="cogs", type="file", example=2500),
     *             @OA\Property(property="qty", type="int", example=30),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the item is successfully created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data item saved successfully"),
     *             @OA\Property(property="result", type="object",
     *                     @OA\Property(property="id", type="string", example="1"),
     *                     @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                     @OA\Property(property="item_id", type="int", example=1),
     *                     @OA\Property(property="cogs", type="file", example=2500),
     *                     @OA\Property(property="qty", type="int", example=30),
     *                 ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Response when the session has expired or the user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=401),
     *             @OA\Property(property="message", type="string", example="Your session has expired or you are not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Response when there are issues on the validation process",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=422),
     *             @OA\Property(property="message", type="string", example="Form validation unsuccessful. Check the error details for futhermore"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="field", type="array",
     *                     @OA\Items(type="string", example="The field is required.")
     *                 ),
     *                 @OA\Property(property="anotherField", type="array",
     *                     @OA\Items(type="string", example="The field must be a string.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Response when there are issues on the server",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * 
     * @param \Illuminate\Http\Request $request The request object containing pagination and filter parameters.
     * @return \Illuminate\Http\Response The response containing the paginated list of coupons.
     */
    public function store(StoreItemStockRequest $request, Item $item)
    {
        $param = $request->validated();
        try {
            $data = $this->itemStockService->createStock($item->id, $param['cogs'], $param['qty']);
            return $this->apiResponse->success(new ItemStockResource($data), 'Item stock created successfully', 201);
        } catch (Exception $ex) {
            return $this->apiResponse->error($ex->getMessage(), 500);
        }
    }

    /**
     * Update the specified item Stock.
     *
     * @OA\Put(
     *     path="/api/v1/item/stock/{uuid}",
     *     summary="Update a item stock",
     *     description="This endpoint allows you to update the details of an existing item stock. Provide the item's UUID and the updated information in the request body to modify attributes such as the item's name and thumbnail.",
     *     operationId="UpdateItemStock",
     *     tags={"Item Stock"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Bearer token for authentication",
     *         @OA\Schema(
     *             type="string",
     *             example="Bearer <your-token-here>"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         required=true,
     *         description="UUID of the data",
     *         @OA\Schema(
     *             type="string",
     *             example="Skfr-4584kndir4-456"
     *         )
     *     ),
     *    @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="cogs", type="file", example=2500),
     *             @OA\Property(property="qty", type="int", example=30),
     *         )
     *     ),
     *    @OA\Response(
     *         response=401,
     *         description="Response when the session has expired or the user is not authenticated.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=401),
     *             @OA\Property(property="message", type="string", example="Your session has expired or you are not authenticated.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the data updated successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data item updated successfully"),
     *             @OA\Property(property="result", type="object",
     *                     @OA\Property(property="id", type="string", example="1"),
     *                     @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                     @OA\Property(property="item_id", type="int", example=1),
     *                     @OA\Property(property="cogs", type="file", example=2500),
     *                     @OA\Property(property="qty", type="int", example=30),
     *                 ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Response when the data was not found.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=404),
     *             @OA\Property(property="message", type="string", example="The data was not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Response when there are issues on the validation process",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=422),
     *             @OA\Property(property="message", type="string", example="Form validation unsuccessful. Check the error details for futhermore"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="field", type="array",
     *                     @OA\Items(type="string", example="The field is required.")
     *                 ),
     *                 @OA\Property(property="anotherField", type="array",
     *                     @OA\Items(type="string", example="The field must be a string.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Response when there are issues on the server",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * @param UpdateItemStockRequest $request The HTTP request containing validated update data.
     * @param ItemStock $ttemStock The ItemStock model instance to update.
     * @return \Illuminate\Http\JsonResponse JSON response with the updated employee data.
     */
    public function update(UpdateItemStockRequest $request, ItemStock $itemStock)
    {
        $param = $request->validated();
        try {
            $data = $this->itemStockService->updateStock($itemStock, $param['cogs'], $param['qty']);
            return $this->apiResponse->success(new ItemStockResource($data), 'Item stock created successfully', 201);
        } catch (Exception $ex) {
            return $this->apiResponse->error($ex->getMessage(), 500);
        }
    }
}
