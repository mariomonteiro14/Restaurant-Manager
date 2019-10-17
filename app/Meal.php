<?php

namespace App;

use App\Http\Resources\Table;
use Illuminate\Database\Eloquent\Model;

class Meal extends Model
{
    protected $fillable = [
        'state',
        'table_number',
        'responsible_waiter_id',
        'total_price_preview',
        'start',
        'end',
    ];


    public function responsible()
    {
        return $this->belongsTo(User::class, 'id','responsible_waiter_id');
    }

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_number','table_number');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'meal_id', 'id');
    }
    public function invoices(){
        return $this->hasOne(Invoices::class, 'meal_id','id');
    }
}
