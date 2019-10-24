<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DocCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // return parent::toArray($request);

        // return [
        //     'maingroup' => [
        //        description": "Consent letter"
        //     ],
        //     'subgroup' => $this->collection,
        // ];
    
    }
}
