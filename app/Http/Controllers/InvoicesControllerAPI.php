<?php

namespace App\Http\Controllers;

use App\Item;
use App\Invoice_items;
use App\Invoices;
use App\Http\Resources\Invoice_items as Invoice_itemsResource;
use App\Http\Resources\Invoices as InvoicesResource;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class InvoicesControllerAPI extends Controller
{
    public function invoices(Request $request)
    {
        $invoices = InvoicesResource::collection(Invoices::orderByRaw("FIELD(state, \"pending\", \"paid\", \"not paid\")")->orderBy('date', 'desc')->paginate(25));

        foreach ($invoices as $invoice) {
            $user = User::find($invoice->meal->responsible_waiter_id);
            if ($user != null) {
                $invoice->meal->responsible_waiter_id = $user->name;
            } else {
                $invoice->meal->responsible_waiter_id = "unknown ID";
            }

            foreach ($invoice->invoice_items as $item) {
                $aux = Item::find($item->item_id);
                if ($aux != null) {
                    $item->item_id = $aux->name;
                }else {
                    $item->item_id = "unknown ID";
                }
            }
        }
        return response()->json([
            'data' => $invoices,
            'total' => $invoices->total(),
            'per_page' => $invoices->perPage(),
            'current_page' => $invoices->currentPage(),
            'last_page' => $invoices->lastPage(),
            'from' => $invoices->firstItem(),
            'to' => $invoices->lastItem(),
            'next_page_url' => $invoices->nextPageUrl(),
            'prev_page_url' => $invoices->previousPageUrl(),
        ]);
    }

    public function invoiceItems(Request $request)
    {
        return Invoice_itemsResource::collection(Invoice_items::all());
    }

    public function pendingInvoices()
    {
        $invoices = InvoicesResource::collection(Invoices::where('state', 'pending')->orderBy('date', 'desc')->paginate(25));
        foreach ($invoices as $invoice) {
            $user = User::find($invoice->meal->responsible_waiter_id);
            if ($user != null) {
                $invoice->meal->responsible_waiter_id = $user->name;
            } else {
                $invoice->meal->responsible_waiter_id = "unknown ID";
            }

            foreach ($invoice->invoice_items as $item) {
                $aux = Item::find($item->item_id);
                if ($aux != null) {
                    $item->item_id = $aux->name;
                } else {
                    $item->item_id = "unknown ID";
                }
            }
        }
        return response()->json([
            'data' => $invoices,
            'total' => $invoices->total(),
            'per_page' => $invoices->perPage(),
            'current_page' => $invoices->currentPage(),
            'last_page' => $invoices->lastPage(),
            'from' => $invoices->firstItem(),
            'to' => $invoices->lastItem(),
            'next_page_url' => $invoices->nextPageUrl(),
            'prev_page_url' => $invoices->previousPageUrl(),
        ]);
    }


    public function storeInvoices($data)
    {
        if (!Auth::user()->isWaiter()) {
            return response()->json('You dont have permissions', 500);
        }
        $data->validate([
            'name' => 'required|min:3|max:255',
            'type' => 'required|in:drink,dish',
            'description' => 'required|min:15|max:255',
            'price' => 'required|numeric|between:0.01,9999999',
            'photo_url' => 'required|file|mimes:jpeg,bmp,png,jpg',
        ]);

        $invoices = new Invoices();
        $invoices->fill($data);
        $invoices->save();

        return response()->json(new InvoicesResource($invoices), 201);
    }

    public function storeInvoiceItem($data)
    {
        if (!Auth::user()->isWaiter()) {
            return response()->json('You dont have permissions', 500);
        }
        $data->validate([
            'invoice_id' => 'required|numeric',
            'item_id' => 'required|numeric',
            'quantity' => 'required|numeric|min:1',
            'unit_price' => 'required|numeric|between:0.01,9999999',
        ]);

        $invoiceItem = new Invoice_items();
        $invoiceItem->fill($data);
        $invoiceItem->subtotal_price = $invoiceItem->unit_price * $invoiceItem->quantity;
        $invoiceItem->save();

        return response()->json(new Invoice_itemsResource($invoiceItem), 201);
    }

    public function updateInvoice(Request $request, $id)
    {
        if (!Auth::user()->isManager() && !Auth::user()->isCashier()) {
            return response()->json('You dont have permissions', 500);
        }
        $data = $request->validate([
            'name' => 'nullable|min:3|max:255|regex:/(^[A-Za-z ]+$)+/',
            'state' => 'required|in:pending,paid,not paid',
            'nif' => 'nullable|numeric|between:100000000,999999999',
        ]);
        if ($request->input('state') == "paid" && (!$request->has('name') || !$request->has('nif'))) {
            return response()->json('name and nif required', 500);
        }

        $invoice = Invoices::findOrFail($id);
        $invoice->fill($data);
        $invoice->save();

        if ($request->input('state') != "pending") {
            $meal = $invoice->meal;
            $meal->state = $request->input('state');
            $meal->save();
        }
        return response()->json($meal, 200);
    }

}
