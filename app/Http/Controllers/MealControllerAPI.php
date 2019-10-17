<?php

namespace App\Http\Controllers;

use App\Invoice_items;
use App\Invoices;
use App\Http\Resources\Order as OrderResouces;
use App\Item;
use App\Order;
use App\Meal;
use App\Http\Resources\Meal as MealResources;
use App\Http\Resources\Item as ItemResources;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MealControllerAPI extends Controller
{
    public function meals()
    {
        $meals = MealResources::collection(Meal::orderby('start','desc')->paginate(30));
        foreach ($meals as $meal) {
            $user = User::find($meal->responsible_waiter_id);
            if ($user != null) {
                $meal->responsible_waiter_id = $user->name;
            } else {
                $meal->responsible_waiter_id = "unknown";
            }

            foreach ($meal->orders as $order) {
                $item = Item::find($order->item_id);
                if ($item != null) {
                    $order->item_id = $item->name;
                    $order->price = $item->price;
                } else {
                    $order->item_id = "unknown Id";
                    $order->price = "---";
                }
                $item = null;
            }
        }
        return response()->json([
            'data' => $meals,
            'total' => $meals->total(),
            'per_page' => $meals->perPage(),
            'current_page' => $meals->currentPage(),
            'last_page' => $meals->lastPage(),
            'from' => $meals->firstItem(),
            'to' => $meals->lastItem(),
            'next_page_url' => $meals->nextPageUrl(),
            'prev_page_url' => $meals->previousPageUrl(),
        ], 200);
    }
    public function myMeals(Request $request)
    {
        $meals = MealResources::collection(Auth::user()->meals()->paginate(20));
        return response()->json([
            'data' => $meals,
            'total' => $meals->total(),
            'per_page' => $meals->perPage(),
            'current_page' => $meals->currentPage(),
            'last_page' => $meals->lastPage(),
            'from' => $meals->firstItem(),
            'to' => $meals->lastItem(),
            'next_page_url' => $meals->nextPageUrl(),
            'prev_page_url' => $meals->previousPageUrl(),
        ], 200);
    }

    public function myActiveMeals(Request $request)
    {

        return Auth::user()->meals->where('state', 'active')->orderBy('start', 'desc');
    }

    public function managerMeals()
    {
        $meals = MealResources::collection(Meal::where('state','like','active')
            ->orWhere('state','like','terminated')
            ->orderBy('start', 'desc')
            ->paginate(20));
        foreach ($meals as $meal) {
            $user = User::find($meal->responsible_waiter_id);
            if ($user != null) {
                $meal->responsible_waiter_id = $user->name;
            } else {
                $meal->responsible_waiter_id = "unknown ID";
            }

            foreach ($meal->orders as $order) {
                $item = Item::find($order->item_id);
                if ($item != null) {
                    $order->item_id = $item->name;
                    $order->price = $item->price;
                } else {
                    $order->item_id = "unknown Id";
                    $order->price = "---";
                }
                $item = null;
            }
        }
        return response()->json([
            'data' => $meals,
            'total' => $meals->total(),
            'per_page' => $meals->perPage(),
            'current_page' => $meals->currentPage(),
            'last_page' => $meals->lastPage(),
            'from' => $meals->firstItem(),
            'to' => $meals->lastItem(),
            'next_page_url' => $meals->nextPageUrl(),
            'prev_page_url' => $meals->previousPageUrl(),
        ], 200);


    }

    public function destroy($id)
    {
        $meal = Meal::findOrFail($id);

        if ($meal->responsible_waiter_id == Auth::id() || !Auth::user()->isManager()) {

            return response()->json('You dont have permissions', 500);
        }
        $meal->delete();
        return response()->json(null, 204);
    }

    public function store(Request $request)
    {
        if (!Auth::user()->isWaiter()) {
            return response()->json('You dont have permissions', 500);
        }

        $id = $request->validate(['table_number' => 'required|numeric']);

        $meal = new Meal();
        $meal->table_number = $request->input('table_number');
        $meal->state = 'active';
        $meal->responsible_waiter_id = Auth::id();
        $meal->start = date("Y-m-d h:i:s");
        $meal->end = null;
        $meal->total_price_preview = 0;

        $meal->save();

        return response()->json(new MealResources($meal), 201);
    }

    public function update(Request $request, $id)
    {
        //$data = $request->validate(['table_number'=> 'required|unique:restaurant_tables']);

        $meal = Meal::findOrFail($id);
        if ($meal->responsible_waiter_id != Auth::id() && !Auth::user()->isManager()) {

            return response()->json('You dont have permissions', 500);
        }
        $meal->update($request->all());
        $meal->end = date('Y-m-d h:i:s');
        $meal->save();

        //TERMINAR MEAL
        if ($request->has("state") && $request->input("state") == "terminated") {
            $invoices = new Invoices();
            $invoices->state = "pending";
            $invoices->meal_id = $id;
            $invoices->date = date("Y-m-d");
            $invoices->total_price = 0.00;
            $invoices->save();

            $orders = Order::where('meal_id', $meal->id)->where('state', 'not like', 'delivered')->get();
            foreach ($orders as $order) {
                $order->state = 'not delivered';
                $order->save();
            }
            $orderedItems = Order::select('item_id', DB::raw('count(*) as total'))
                ->where('meal_id', $meal->id)
                ->where('state', 'like', 'delivered')
                ->groupBy('item_id')
                ->get();
            //CRIAR INVOICE_ITEMS
            foreach ($orderedItems as $order) {
                $invoice = new Invoice_items();
                $invoice->invoice_id = $invoices->id;

                $item = Item::findOrFail($order->item_id);
                $invoice->item_id = $item->id;
                $invoice->unit_price = $item->price;

                $invoice->quantity = $order->total;
                $invoice->sub_total_price = $item->price * $order->total;
                $invoices->total_price += $invoice->sub_total_price;

                $invoice->save();
            }
            $meal->total_price_preview = $invoices->total_price;
            $meal->save();
            $invoices->save();
        }

        return response()->json(new MealResources($meal), 200);
    }


    public function orders(Request $request, $id)
    {
        $meal = Meal::findOrFail($id);

        return response()->json(OrderResouces::collection($meal->orders), 200);
    }

    public function ordersNotDelivered($id)
    {
        $meal = Meal::findOrFail($id);

        return response()->json(Order::where('meal_id', $meal->id)->where('state', 'not like', 'delivered')->get(), 200);
    }

    public function itemsOrdered($id)
    {

        $itemsOrdered = Order::where('meal_id', $id)->pluck('item_id');
        return response()->json(ItemResources::collection(Item::whereIn('id', $itemsOrdered)->get()), 200);
    }


}
