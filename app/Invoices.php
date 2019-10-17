<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class invoices extends Model
{
    protected $fillable = [
        'state',
        'meal_id',
        'nif',
        'name',
        'date',
        'total_price',
    ];
    public function invoice_items()
    {
        return $this->hasMany(Invoice_items::class, 'invoice_id','id');
    }
    public function meal(){
        return $this->hasOne(Meal::class, 'id','meal_id');
    }
}
