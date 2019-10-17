<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'state',
        'item_id',
        'meal_id',
        'responsible_cook_id',
        'start',
        'end',
    ];
    public function meal()
    {
        return $this->hasOne(Meal::class, 'id','meal_id');
    }
    public function item()
    {
        return $this->hasOne(Item::class, 'id','item_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'responsible_cook_id');
    }

}
