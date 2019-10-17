<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invoice_items extends Model
{
    public $timestamps = false;
    protected $fillable = [
        'invoice_id',
        'item_id',
        'quantity',
        'unit_price',
        'subtotal_price',
    ];
    public function invoice()
    {
        return $this->belongsTo(Invoices::class, 'id','invoice_id');
    }
    public function item(){
        return $this->belongsTo(Item::class, 'id','item_id');
    }
}
