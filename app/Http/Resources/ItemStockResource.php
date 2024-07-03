<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemStockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'item_name' => $this->item->name,
            'cogs' => $this->cogs,
            'qty' => $this->qty,
        ];
    }

    /**
     * Format a collection of resources.
     *
     * @param \Illuminate\Support\Collection $resource
     * @return array<string, mixed>
     */
    public static function collection($resource)
    {
        return [
            'data' => $resource->map(function ($model) {
                return [
                    'id' => $model->id,
                    'uuid' => $model->uuid,
                    'item_name' => $model->item->name,
                    'cogs' => $model->cogs,
                    'qty' => $model->qty,
                ];
            }),
            'pagination' => [
                'current_page' => $resource->currentPage(),
                'per_page' => $resource->perPage(),
                'total' => $resource->total(),
            ],
        ];
    }
}
