<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\DashboardController;
use App\Http\Controllers\API\KitchenController;
use App\Http\Controllers\API\MenuController;
use App\Http\Controllers\API\PesananController;

Route::prefix('customer')->group(function(){
    Route::get('/get_order/{code}', [PesananController::class, 'get_qr_code']);
    // order
    Route::put('/update_order/{id}', [PesananController::class, 'update_order']);
    Route::get('/get_menu/{id}', [MenuController::class, 'get_menu']);
    
    // order detail
    Route::post('/insert_order_detail', [PesananController::class, 'insert_order_detail']);
    Route::delete('/delete_order_detail/{id}', [PesananController::class, 'delete_order_detail']);
    Route::put('/update_order_detail/{id}', [PesananController::class, 'update_order_detail']);
    Route::put('/final_order_detail/{id}', [PesananController::class, 'final_order_detail']);
});

Route::prefix('menu')->group(function(){
    // Kategori Menu
    Route::get('/get_all_category', [MenuController::class, 'get_all_category']);

    Route::get('/get_menu/{id}', [MenuController::class, 'get_menu']);
    Route::get('/get_all_menu', [MenuController::class, 'get_all_menu']);
});


Route::post('/register', [AuthController::class, 'register']);
    
Route::post('/login', [AuthController::class, 'login']);

Route::get('/set_role', [AuthController::class, 'set_role']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/home', function(Request $request) {
        return $request->user();
    });

    Route::get('dashboard', [DashboardController::class, 'get_dashboard']);
    Route::post('report', [DashboardController::class, 'get_report']);

    Route::prefix('auth')->group(function(){
        Route::get('get_user/{username}', [AuthController::class, 'get_user']);
        Route::get('get_all_user', [AuthController::class, 'get_all_user']);
        Route::get('set_role', [AuthController::class, 'set_role']);
        
        Route::put('update_user/{id}', [AuthController::class, 'edit_user']);
        Route::post('reset_password', [AuthController::class, 'reset_password']);
        Route::post('change_password', [AuthController::class, 'change_password']);
        Route::post('delete_user', [AuthController::class, 'delete_user']);
    });
    
    
    Route::prefix('menu')->group(function(){
        // Kategori Menu
        Route::post('/create_category', [MenuController::class, 'create_category']);
        Route::get('/get_category/{id}', [MenuController::class, 'get_category']);
        Route::delete('/delete_category/{id}', [MenuController::class, 'delete_category']);
        Route::put('/update_category/{id}', [MenuController::class, 'update_category']);
    
        // Menu
        Route::post('/insert_menu', [MenuController::class, 'insert_menu']);
        Route::put('/edit_menu/{id}', [MenuController::class, 'edit_menu']);
        Route::delete('/delete_menu/{id}', [MenuController::class, 'delete_menu']);
        
        Route::delete('/delete_attribute/{nama}', [MenuController::class, 'delete_attribute']);
        Route::get('/get_name_attribute', [MenuController::class, 'get_name_attribute']);
        
        Route::get('/get_all_meja/', [MenuController::class, 'get_all_meja']);
        Route::get('/get_meja/{id}', [MenuController::class, 'get_meja']);
        Route::put('/edit_meja/{id}', [MenuController::class, 'edit_meja']);
        Route::post('/insert_meja/', [MenuController::class, 'insert_meja']);
        Route::delete('/delete_meja/{id}', [MenuController::class, 'delete_meja']);
    });
    
    Route::prefix('order')->group(function(){
        Route::post('/insert_order', [PesananController::class, 'insert_order']);
        Route::get('/get_order/{id}', [PesananController::class, 'get_order']);
        Route::get('/get_all_order', [PesananController::class, 'get_all_order']);
        Route::put('/update_order/{id}', [PesananController::class, 'update_order']);
        
        Route::post('/insert_order_detail/{id}', [PesananController::class, 'insert_order_detail']);

        Route::get('/history_all_order', [PesananController::class, 'history_all_order']);
        Route::get('/batal_order/{id}', [PesananController::class, 'batal_order']);
        Route::put('/final_order/{id}', [PesananController::class, 'final_pembayaran']);
    });

    Route::prefix('kitchen')->group(function(){
        Route::get('/get_order_detail', [KitchenController::class, 'get_order_detail']);
        Route::put('/confirmation_menu/{id}', [KitchenController::class, 'confirmation_menu']);
    });

});
