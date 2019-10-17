<?php

namespace App\Http\Controllers;

use App\Item;
use App\Meal;
use App\Order;
use App\User;
use Illuminate\Http\Request;
use App\Http\Resources\Order as OrderResources;
use Illuminate\Support\Facades\Auth;

class OrderControllerAPI extends Controller
{

    public function orders(Request $request){
        return OrderResources::collection(Order::all());
    }
    public function myOrders(){
        if (!Auth::user()->isWaiter()){
            return response()->json('You dont have permissions', 403);
        }

        $meals_id = Meal::where('responsible_waiter_id', Auth::id())->where('state','like', 'active')->pluck('id');

        $orders = Order::whereIn('meal_id', $meals_id)->whereIn('state', ['pending', 'confirmed'])
            ->orderBy('start', 'desc')->paginate(10);

        foreach ($orders as $order){
            $item = Item::find($order->item_id);

            if ($item != null){
                $order->item_id = $order->item->name;
            }else{
                $order->item_id = "ERROR: item not found!";
            }
        }
        $orders = OrderResources::collection($orders);
        return response()->json([
            'data' => $orders,
            'total' => $orders->total(),
            'per_page' => $orders->perPage(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'from' => $orders->firstItem(),
            'to' => $orders->lastItem(),
            'next_page_url' => $orders->nextPageUrl(),
            'prev_page_url' => $orders->previousPageUrl(),
        ], 200);
    }

    public function waiterPreparedOrders(Request $request){
        if (!Auth::user()->isWaiter()){
            return response()->json('You dont have permissions', 403);
        }

        $meals_id = Meal::where('responsible_waiter_id', Auth::id())->where('state','like', 'active')->pluck('id');

        $orders = Order::whereIn('meal_id', $meals_id)->where('state', 'prepared')
            ->orderBy('start', 'desc')->paginate(10);

        foreach ($orders as $order){
            $item = Item::find($order->item_id);

            if ($item != null){
                $order->item_id = $order->item->name;
            }else{
                $order->item_id = "ERROR: item not found!";
            }
        }
        $orders = OrderResources::collection($orders);
        return response()->json([
            'data' => $orders,
            'total' => $orders->total(),
            'per_page' => $orders->perPage(),
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'from' => $orders->firstItem(),
            'to' => $orders->lastItem(),
            'next_page_url' => $orders->nextPageUrl(),
            'prev_page_url' => $orders->previousPageUrl(),
        ], 200);
    }

    public function cookOrders(Request $request){
        if(!Auth::user()->isCook()){
            return response()->json(['message' => 'Not allowed', 403]);
        }
        $id = Auth::id();
        $orders = Order::where('state', 'in preparation')->where('responsible_cook_id', Auth::id())->orWhere('state', '=', 'confirmed')->whereNull('responsible_cook_id')->orderByRaw("FIELD(state, \"in preparation\", \"confirmed\")")->orderBy('start', 'desc')->paginate(15);
        foreach ($orders as $order){
            $item = Item::find($order->item_id);
            $cook = User::find($order->responsible_cook_id);

            if ($item != null){
                $order->item_id = $order->item->name;

            }else{
                $order->item_id = "ERROR: item not found!";
            }
            if($cook != null){
                $order->responsible_cook_id = $order->user->name;
            }
        }
        return OrderResources::collection($orders);
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $meal = Meal::findOrFail($order->meal_id);

        if ($meal->responsible_waiter_id != Auth::id()){
            return response()->json('You dont have permissions', 500);
        }

        $item = Item::findOrFail($order->item_id);

        $meal->total_price_preview = $meal->total_price_preview - $item->price;

        $meal->save();
        $order->delete();

        return response()->json(null, 204);
    }
    public function store(Request $request, $id){
        $meal = Meal::findOrFail($id);

        if (!Auth::user()->isWaiter() || $meal->responsible_waiter_id != Auth::id()){
            return response()->json('You dont have permissions', 500);
        }
        $request->validate(['item_id'=> 'required|numeric']);

        $item = Item::findOrFail($request->input('item_id'));

        $order = new Order();
        $order->state  = "pending";
        $order->item_id  = $request->input('item_id');
        $order->meal_id  = $id;
        $order->start  = date("Y-m-d h:i:s");
        $order->save();

        $meal->total_price_preview += $item->price;
        $meal->save();

        return response()->json(new OrderResources($order), 200);
    }
    public function update(Request $request, $id){

        $request->validate(['state' => 'required|in:pending,confirmed,in preparation,prepared,delivered,not delivered']);

        if ($request->input('state') != "delivered"){
            if (Auth::id() != $request->input('responsible_id')){
                return response()->json('You do not have permissions', 403);
            }
        }
        $order = Order::findOrFail($id);
        $order->state = $request->input('state');
        if($request->input('state') == "in preparation"){
            $order->responsible_cook_id = Auth::id();
        }
        $order->save();
        if($order->state == "prepared" || $order->state == "in preparation"){
            return response()->json(['responsible_waiter_id' => $order->meal->responsible_waiter_id], 200);
        }
        return response()->json(new OrderResources($order), 200);
    }

    public function confirm(Request $request, $id){
        $order = Order::find($id);
        if($order == null){
            return response()->json(['state' => 'deleted'], 200);
        }
        $order->state = "confirmed";
        $order->save();
        return response()->json(['state' => 'confirmed'], 200);
    }
}
