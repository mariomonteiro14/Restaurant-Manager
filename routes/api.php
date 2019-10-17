<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([
	'middleware' => 'auth:api'
], function() {

    //Manager
    Route::group([
        'middleware' => 'manager'], function(){
            Route::post('register', 'UserControllerAPI@register')->name('register');
            Route::get('users', 'UserControllerAPI@users');
            Route::delete('users/{id}', 'UserControllerAPI@destroy');

            //TABLES
            Route::get('tables', 'TableControllerAPI@tables');
            Route::post('tables', 'TableControllerAPI@store');
            Route::put('tables/{id}', 'TableControllerAPI@update');
            Route::delete('tables/{id}', 'TableControllerAPI@destroy');

            Route::get('/manager/meals', 'MealControllerAPI@managerMeals');
            Route::get('meals', 'MealControllerAPI@meals');

            //ITEMS
            Route::get('items/{id}', 'ItemControllerAPI@item');
            Route::post('items', 'ItemControllerAPI@store');
            Route::post('items/{id}', 'ItemControllerAPI@update');
            Route::delete('items/{id}', 'ItemControllerAPI@destroy');
    });

    //Cook
    Route::group([
        'middleware' => 'cook'], function(){
            Route::get('cook/orders', 'OrderControllerAPI@cookOrders');
    });

    //Waiter
    Route::group([
        'middleware' => 'waiter'], function(){
            Route::get('me/meals', 'MealControllerAPI@myMeals');
            Route::get('me/meals/{id}/items', 'MealControllerAPI@itemsOrdered');
            Route::get('me/meals/{id}/orders', 'MealControllerAPI@orders');
            Route::get('me/orders', 'OrderControllerAPI@myOrders');
            Route::get('me/orders/prepared', 'OrderControllerAPI@waiterPreparedOrders');
            Route::delete('me/orders/{id}', 'OrderControllerAPI@destroy');
            Route::get('me/orders/{id}/confirm', 'OrderControllerAPI@confirm');
            Route::post('me/meals/{id}/orders', 'OrderControllerAPI@store');
            Route::get('tablesAvailables', 'TableControllerAPI@tablesAvailables');
            Route::get('me/meals/{id}/ordersNotDelivered', 'MealControllerAPI@ordersNotDelivered');
    });

    //Cashier
    Route::group([
        'middleware' => 'cashier'], function(){
            Route::get('invoices/pending', 'InvoicesControllerAPI@pendingInvoices');
    });

    //Waiter-Manager
    Route::group([
        'middleware' => 'waiter_manager'], function(){
            //MEALS partilhado waiter-manager
            Route::post('me/meals', 'MealControllerAPI@store');
            Route::put('me/meals/{id}', 'MealControllerAPI@update');
    });

    //Waiter-Cook
    Route::group([
        'middleware' => 'waiter_cook'], function(){
            //waiter-cook
            Route::put('me/orders/{id}', 'OrderControllerAPI@update');
    });

    //Manager-Cashier
    Route::group([
        'middleware' => 'manager_cashier'], function(){
        //INVOICES manager-cashier
        Route::get('invoices', 'InvoicesControllerAPI@invoices');
        Route::put('invoices/{id}', 'InvoicesControllerAPI@updateInvoice');
    });

    //USERS
	Route::get('logout', 'UserControllerAPI@logout');
    Route::get('users/me', 'UserControllerAPI@myProfile');
    Route::put('users/{id}', 'UserControllerAPI@updateStatus');
    Route::post('changePassword', 'UserControllerAPI@changePassword')->name('changePassword');
    Route::post('editProfile', 'UserControllerAPI@editProfile');
    Route::post('shift', 'UserControllerAPI@shift');

});

Route::get('items', 'ItemControllerAPI@index');

Route::post('login', 'UserControllerAPI@login')->name('login');
Route::post('register/activate/{token}', 'UserControllerAPI@registerActivate');

Route::get('checkRegisted/{token}', 'UserControllerAPI@checkRegisted');
