<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
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
            'name' => $this->name,
            'phone_no' => $this->phone_no,
            'point' => $this->point,
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
                    'name' => $model->name,
                    'phone_no' => $model->phone_no,
                    'point' => $model->point,
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
