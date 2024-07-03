<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesDetailsResource extends JsonResource
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
            'sales_order' => $this->sales->uuid,
            'item_name' => $this->item->name,
            'qty' => $this->qty,
            'price' => $this->price,
            'sub_total' => $this->sub_total,
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
                    'sales_order' => $model->sales->uuid,
                    'item_name' => $model->item->name,
                    'qty' => $model->qty,
                    'price' => $model->price,
                    'sub_total' => $model->sub_total,
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
