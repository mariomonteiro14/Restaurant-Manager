<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Item;
use App\Http\Resources\Item as ItemResource;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class ItemControllerAPI extends Controller
{
    public function index(Request $request){
    	return ItemResource::collection(Item::all());
    }

    public function item(Request $request, $id){
        return new ItemResource(Item::findOrFail($id));
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isManager()){
            return response()->json('You dont have permissions', 500);
        }
        $data = $request->validate([
            'name' => 'required|min:3|max:255',
            'type' => 'required|in:drink,dish',
            'description' => 'required|min:15|max:255',
            'price' => 'required|numeric|between:0.01,9999999',
            'photo_url' => 'required|file|mimes:jpeg,bmp,png,jpg',
        ]);


        if($request->file('photo_url')){
            $data['photo_url'] = str_random(16).'.'.$request->file('photo_url')->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('items', $request->file('photo_url'), $data['photo_url']);
        }

        $item = new Item();
        $item->fill($data);
        $item->save();

        return response()->json(new ItemResource($item), 201);
    }

    public function update(Request $request, $id)
    {
        if (!Auth::user()->isManager()){
            return response()->json('You dont have permissions', 500);
        }
        $data = $request->validate([
            'name' => 'required|min:3|max:255',
            'type' => 'required|in:drink,dish',
            'description' => 'required|min:15|max:255',
            'price' => 'required|numeric|between:0.01,9999999',
            'newPhoto' => 'nullable|file|mimes:jpeg,bmp,png,jpg'
        ]);

        $item = Item::findOrFail($id);
        $item->update($data);

        if($request->has('newPhoto')){
            //eliminar foto antiga
            Storage::disk('public')->delete('items/'.$item->photo_url);
            //guarnar nova foto
            $item->photo_url = str_random(16).'.'.$request->file('newPhoto')->getClientOriginalExtension();
            Storage::disk('public')->putFileAs('items', $request->file('newPhoto'), $item->photo_url);

            $item->save();
        }
        return response()->json(new ItemResource($item), 200);
    }
    public function destroy($id)
    {
        if (!Auth::user()->isManager()){
            return response()->json('You dont have permissions', 500);
        }
        $item = Item::findOrFail($id);
        $item->delete();
        return response()->json(null, 204);
    }
}
