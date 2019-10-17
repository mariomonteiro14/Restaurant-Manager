<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Invoices extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'=>$this->id,
            'state' => $this->state,
            'meal_id' => $this->meal_id,
            'nif' => $this->nif,
            'name' => $this->name,
            'date' => $this->date,
            'total_price' => $this->total_price,
            'meal'=> $this->meal,
            'items'=> $this->invoice_items,
        ];
    }
}
