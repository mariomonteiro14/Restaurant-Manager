<?php

namespace App\Http\Controllers;

use App\Meal;
use App\RestaurantTable as Table;
use Illuminate\Http\Request;
use App\Http\Resources\Table as TablesResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TableControllerAPI extends Controller
{

    public function tables(Request $request){
        return TablesResource::collection(Table::all());
    }
    public function tablesAvailables(Request $request)
    {
        $occupiedTables = Meal::where('state','active')->pluck('table_number');
        return TablesResource::collection(Table::whereNotIn('table_number', $occupiedTables)->get());
    }

    public function destroy($id)
    {
        if (!Auth::user()->isManager()){
            return response()->json('You dont have permissions', 500);
        }
        $table = Table::where('table_number', $id)->first();
        $table->delete();
        return response()->json(null, 204);
    }
    public function store(Request $request){
        if (!Auth::user()->isManager()){
            return response()->json('You dont have permissions', 500);
        }
        $id = $request->validate(['table_number'=> 'required|unique:restaurant_tables']);
        $table = new Table();
        $table->fill($id);
        $table->save();

        return response()->json(new TablesResource($table), 201);
    }
    public function update(Request $request, $id){
        if (!Auth::user()->isManager()){
            return response()->json('You dont have permissions', 500);
        }
        if ($request->has('table_number') && $request->input('table_number') == $id){
            return response()->json(null, 200);
        }
        $data = $request->validate(['table_number'=> 'required|unique:restaurant_tables']);
        $table = Table::findOrFail($id);
        $table->update($data);

        return response()->json(new TablesResource($table), 200);
    }
}
