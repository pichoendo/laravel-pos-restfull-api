<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SaleItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'item_id' => $this->item_id,
            'qty' => $this->qty,
            'price' => $this->price,
            'item_id' => $this->item_id,
            'sub_total' => $this->sub_total
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
                return (new SaleItemResource($model))->toArray('');
            }),
            'pagination' => [
                'current_page' => $resource->currentPage(),
                'per_page' => $resource->perPage(),
                'total' => $resource->total(),
            ],
        ];
    }

    /**
     * Format a collection of resources.
     *
     * @param \Illuminate\Support\Collection $resource
     * @return array<string, mixed>
     */
    public static function list($resource)
    {
        return [
             $resource?->map(function ($model) {
                return (new SaleItemResource($model))->toArray('');
            }),
        ];
    }
}
