<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\MenuController;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::get('/set_role', [AuthController::class, 'set_role']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/home', function(Request $request) {
        return $request->user();
    });
    
    Route::get('/logout', [AuthController::class, 'logout']);
    
    
    Route::prefix('menu')->group(function(){
        Route::post('/create_category', [MenuController::class, 'create_category']);
        Route::get('/get_category/{id}', [MenuController::class, 'get_category']);
        Route::get('/get_all_category', [MenuController::class, 'get_all_category']);
        Route::delete('/delete_category/{id}', [MenuController::class, 'delete_category']);
        Route::post('/update_category', [MenuController::class, 'update_category']);
    
        Route::get('/get_menu/{id}', [MenuController::class, 'get_menu']);

        Route::get('/get_all_menu', [MenuController::class, 'get_all_menu']);
    });


});
