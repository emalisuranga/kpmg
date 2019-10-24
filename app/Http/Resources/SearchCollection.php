<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\Resource;

class SearchCollection  extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'available' => false,
            'data' => $this->collection,
            'meta' => [
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total'=> $this->total()
            ]
        ];
    }
}
