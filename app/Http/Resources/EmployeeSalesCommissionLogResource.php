<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeSalesCommissionLogResource extends JsonResource
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
            'employee_name' => $this->employee->name,
            'description' => $this->description,
            'value' => $this->value,
            'type' => $this->type == 1 ? "add" : "sub",
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
                    'employee_name' => $model->employee->name,
                    'description' => $model->description,
                    'value' => $model->value,
                    'type' => $model->type == 1 ? "add" : "sub",
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
