<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PolicyCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'policies' => PolicyResource::collection($this->whenLoaded('policies')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}