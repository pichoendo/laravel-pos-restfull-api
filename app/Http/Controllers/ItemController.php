<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Http\Requests\StoreItemRequest;
use App\Http\Requests\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Http\Resources\ItemStockResource;
use App\Responses\ApiResponse;
use App\Services\ItemService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ItemController extends PaginateController implements HasMiddleware
{

    /**
     * ItemController constructor.
     *
     * 
     * This constructor initializes the `EmployeeSalesCommissionLogController` by injecting 
     * the `EmployeeComissionService` and `ApiResponse` dependencies. The `EmployeeComissionService` 
     * is used for handling EmployeeComission CRUD logic, and the `ApiResponse` 
     * is passed to the parent controller to handle standardized API responses.
     *
     *  
     * @param ItemService $itemService
     * @param  \App\Http\Responses\ApiResponse  $apiResponse  The service for standardized API responses.
     */
    public function __construct(public ItemService $itemService, ApiResponse $apiResponse)
    {
        parent::__construct($apiResponse);
    }
    /**
     * Define the middleware applied to the controller methods.
     *
     * This method returns an array of middleware configurations:
     * - The 'manage_item' permission is required to access the 'update', 'store', and 'destroy' methods.
     * - The 'manage_item' or 'consume_item' permissions are required to access the 'index', 'show', and 'getItemListOfItems' methods.
     *
     * @return array The array of middleware applied to the controller methods.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('permission:manage_item', only: ['update',  'store', 'destroy']), // apply for manage_item
            new Middleware('permission:manage_item|consume_item', only: ['index', 'show', 'getItemListOfItems',]), // apply for manage_item & consume_item
        ];
    }

    /**
     * Display a listing of the items.
     *
     * @OA\Get(
     *     path="/api/v1/item",
     *     summary="Retrieve a list of items",
     *     description="This endpoint is used to retrieve a paginated list of items. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by keyword using the 'search' parameter",
     *     operationId="GetItemList",
     *     tags={"Item"},
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
     *     @OA\Response(
     *         response=200,
     *         description="Response when the list of items is successfully retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data item fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="name", type="string", example="Item 1"),
     *                         @OA\Property(property="price", type="int", example=4000),
     *                         @OA\Property(property="image_file", type="file", example=""),
     *                         @OA\Property(property="item_id", type="int", example=1),
     *                         @OA\Property(property="cogs", type="file", example=2500),
     *                         @OA\Property(property="qty", type="int", example=30),
     *                     ),
     *                 ),
     *                 @OA\Property(property="paginate", type="object",
     *                     @OA\Property(property="current_page", type="int", example="1"),
     *                     @OA\Property(property="per_page", type="int", example="10"),
     *                     @OA\Property(property="total", type="int", example="20")
     *                 )
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $perPage = $request->input('per_page', $this->PER_PAGE);
        $page = $request->input('page', $this->START_PAGE);
        $search = $request->input('search', "");

        $data = $this->itemService->getData($search, $page, $perPage);
        return $this->apiResponse->success(ItemResource::collection($data), 'Fetch successfully', 200);
    }



    /**
     * Store a newly created item in storage.
     *
     * 
     * @OA\Post(
     *     path="/api/v1/item",
     *     summary="Save a item",
     *     description="The endpoint for create a item.",
     *     operationId="SaveItem",
     *     tags={"Item"},
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
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","price","cogs","image_file","item_id","cogs","qty"},
     *             @OA\Property(property="name", type="string", example="Item 1"),
     *             @OA\Property(property="price", type="int", example=4000),
     *             @OA\Property(property="image_file", type="file", example=""),
     *             @OA\Property(property="item_id", type="int", example=1),
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
     *                     @OA\Property(property="name", type="string", example="Item 1"),
     *                     @OA\Property(property="price", type="int", example=4000),
     *                     @OA\Property(property="image_file", type="file", example=""),
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
     * @param StoreItemRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreItemRequest $request)
    {
        $params = $request->validated();
        try {
            $data = $this->itemService->create($params);
            return $this->apiResponse->success(new ItemResource($data), 'Item created successfully', 201);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to create item. Please try again later.', 500);
        }
    }

    /**
     * Display the specified item.
     *
     * 
     * @OA\Get(
     *     path="/api/v1/item/{uuid}",
     *     summary="Retrieve a item by its UUID.",
     *     description="This endpoint allows you to retrieve details of a specific item using its UUID. Provide the UUID of the item in the request to get information such as the item's name, description, and any other relevant details",
     *     operationId="GetItem",
     *     tags={"Item"},
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
     *                     @OA\Property(property="name", type="string", example="Item 1"),
     *                     @OA\Property(property="price", type="int", example=4000),
     *                     @OA\Property(property="image_file", type="file", example=""),
     *                     @OA\Property(property="item_id", type="int", example=1),
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
     * @param Item $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Item $item)
    {
        return $this->apiResponse->success(new ItemResource($item), 'Fetch successfully', 200);
    }

    /**
     * Update the specified item in storage.
     *
     * 
     * @OA\Put(
     *     path="/api/v1/item/{uuid}",
     *     summary="Update a item",
     *     description="This endpoint allows you to update the details of an existing item. Provide the item's UUID and the updated information in the request body to modify attributes such as the item's name and thumbnail.",
     *     operationId="UpdateItem",
     *     tags={"Item"},
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
     *             @OA\Property(property="name", type="string", example="Item 1"),
     *             @OA\Property(property="price", type="int", example=4000),
     *             @OA\Property(property="image_file", type="file", example=""),
     *             @OA\Property(property="item_id", type="int", example=1),
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
     *                     @OA\Property(property="name", type="string", example="Item 1"),
     *                     @OA\Property(property="price", type="int", example=4000),
     *                     @OA\Property(property="image_file", type="file", example=""),
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
     * @param UpdateItemRequest $request
     * @param Item $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateItemRequest $request, Item $item)
    {
        $params = $request->validated();

        try {
            $data = $this->itemService->update($item, $params);
            return $this->apiResponse->success(new ItemResource($data), 'Item updated successfully', 201);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to update item. Please try again later.', 500);
        }
    }

    /**
     * Remove the specified item from storage.
     *
     * 
     * * @OA\Delete(
     *     path="/api/v1/item/{uuid}",
     *     summary="Delete a item",
     *     description="This endpoint allows you to delete a specific item from the system. Provide the UUID of the item you wish to remove, and the item will be permanently deleted from the database",
     *     operationId="DeleteItem",
     *     tags={"Item"},
     *     @OA\Parameter(name="Authorization",
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
     *         response=404,
     *         description="Response when the data was not found.",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=404),
     *             @OA\Property(property="message", type="string", example="The data was not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Response when the data deleted successfully.",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data item deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Response when there are issues on the validation process",
     *         @OA\JsonContent(type="object",
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
     *         description="Response when there are issues on the server.",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="code", type="int", example=500),
     *             @OA\Property(property="message", type="string", example="Oops, something went wrong on our server. Please try again later.")
     *         )
     *     )
     * )
     * 
     * @param Item $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Item $item)
    {
        try {
            $this->itemService->destroy($item);
            return $this->apiResponse->success(null, 'Deleted successfully', 201);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to delete item. Please try again later.', 500);
        }
    }

    /**
     * Get the stock information for the specified item.
     *
     * @OA\Get(
     *     path="/api/v1/item/{uuid}/stocks",
     *     summary="Retrieve list stock of a items",
     *     description="This endpoint retrieves a list stock of a items associated with a specific item. Provide the UUID of the item to get a detailed list of all items that belong to it, including their attributes and relevant information.",
     *     operationId="GetStockItems",
     *     tags={"Item"},
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
     *     @OA\Response(
     *         response=200,
     *         description="Response when the data retrived successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Item items data fetched successfully."),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="cogs", type="string", example="2500"),
     *                         @OA\Property(property="qty", type="string", example="34")
     *                     ),
     *                 ),
     *                 @OA\Property(property="paginate", type="object",
     *                     @OA\Property(property="current_page", type="int", example="1"),
     *                     @OA\Property(property="per_page", type="int", example="10"),
     *                     @OA\Property(property="total", type="int", example="20")
     *                 )
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
     * @param Item $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function getItemStocks(Item $item)
    {
        try {
            $data = $this->itemService->getItemStocks($item);
            return $this->apiResponse->success(ItemStockResource::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to fetch data. Please try again later.', 500);
        }
    }

    /**
     * Get the top-selling items.
     *
     * @OA\Get(
     *     path="/api/v1/item/topSelling",
     *     summary="Retrieve a list of top selling items",
     *     description="This endpoint is used to retrieve a paginated list of top selling items. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by keyword using the 'search' parameter",
     *     operationId="GetItemTopSellingist",
     *     tags={"Item"},
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
     *     @OA\Response(
     *         response=200,
     *         description="Response when the list of items is successfully retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data item fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="name", type="string", example="Item 1"),
     *                         @OA\Property(property="price", type="int", example=4000),
     *                         @OA\Property(property="image_file", type="file", example=""),
     *                         @OA\Property(property="item_id", type="int", example=1),
     *                         @OA\Property(property="cogs", type="file", example=2500),
     *                         @OA\Property(property="qty", type="int", example=30),
     *                     ),
     *                 ),
     *                 @OA\Property(property="paginate", type="object",
     *                     @OA\Property(property="current_page", type="int", example="1"),
     *                     @OA\Property(property="per_page", type="int", example="10"),
     *                     @OA\Property(property="total", type="int", example="20")
     *                 )
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTopSellingItems(Request $request)
    {

        try {
            $perPage = $request->input('per_page', $this->PER_PAGE);
            $page = $request->input('page', $this->START_PAGE);
            $dateRange = $request->input('dateRange', null);

            $data = $this->itemService->getTopSellingItems($page, $perPage, $dateRange);
            return $this->apiResponse->success(Item::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to fetch data. Please try again later.', 500);
        }
    }

    /**
     * Get a list of out-of-stock items.
     *
     * @OA\Get(
     *     path="/api/v1/item/lowStock",
     *     summary="Retrieve a list of low stock items",
     *     description="This endpoint is used to retrieve a paginated list of low stock items. You can set the number of items per page using the 'perPage' parameter and specify the page using the 'page' parameter. It is also possible to filter the results by keyword using the 'search' parameter",
     *     operationId="GetItemLowStockist",
     *     tags={"Item"},
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
     *     @OA\Response(
     *         response=200,
     *         description="Response when the list of items is successfully retrieved",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Data item fetched successfully"),
     *             @OA\Property(property="result", type="object",
     *                 @OA\Property(property="data", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="string", example="1"),
     *                         @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                         @OA\Property(property="name", type="string", example="Item 1"),
     *                         @OA\Property(property="price", type="int", example=4000),
     *                         @OA\Property(property="image_file", type="file", example=""),
     *                         @OA\Property(property="item_id", type="int", example=1),
     *                         @OA\Property(property="cogs", type="file", example=2500),
     *                         @OA\Property(property="qty", type="int", example=30),
     *                         @OA\Property(property="stocks", type="array",
     *                                   @OA\Items(type="object",
     *                                      @OA\Property(property="id", type="string", example="1"),
     *                                      @OA\Property(property="uuid", type="string", example="Skfr-4584kndir4-456"),
     *                                      @OA\Property(property="cogs", type="string", example="2500"),
     *                                      @OA\Property(property="qty", type="string", example="34")
     *                                   ),
     *                          )
     *                     ),
     *                 ),
     *                 @OA\Property(property="paginate", type="object",
     *                     @OA\Property(property="current_page", type="int", example="1"),
     *                     @OA\Property(property="per_page", type="int", example="10"),
     *                     @OA\Property(property="total", type="int", example="20")
     *                 )
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
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOutOfStockItems(Request $request)
    {

        try {
            $perPage = $request->input('per_page', $this->PER_PAGE);
            $page = $request->input('page', $this->START_PAGE);

            $data = $this->itemService->getOutOfStockItems($page, $perPage);
            return $this->apiResponse->success(Item::collection($data), 'Fetch successfully', 200);
        } catch (Exception $ex) {
            return $this->apiResponse->error('Failed to fetch data. Please try again later.', 500);
        }
    }
}
