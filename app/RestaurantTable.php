<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class RestaurantTable extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'table_number';

    protected $fillable = [
        'table_number',];

    public function table()
    {
        return $this->hasMany(Meal::class, 'table_number','table_number');
    }
}
