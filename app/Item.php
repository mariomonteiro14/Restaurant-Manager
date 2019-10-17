<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use SoftDeletes;

    protected $fillable = [
    	'type',
    	'name',
    	'description',
    	'photo_url',
        'price',
    ];

    protected function orders(){
        return $this->belongsToMany(Order::class, "item_id", id);
    }
}
