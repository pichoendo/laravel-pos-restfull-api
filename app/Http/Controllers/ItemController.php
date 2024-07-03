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
     *
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $query = Item::query();
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        $query = $query->paginate($perPage);

        return APIResponse::success(ItemResource::collection($query), 'Fetch successfully', 200);
    }


    /**
     *
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreItemRequest  $request
     * @return \Illuminate\Http\Response
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
     *
     * Display the specified resource.
     *
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function show(Item $item)
    {
        return APIResponse::success(new ItemResource($item), 'Fetch successfully', 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateItemRequest  $request
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Item  $item
     * @return \Illuminate\Http\Response
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
