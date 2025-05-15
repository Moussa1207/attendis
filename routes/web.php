<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DastyleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/*Route::get('/', function () {
    return view('welcome');
});*/
Route::get('/',[DastyleController::class,'authlogin']);
Route::get('/auth500',[DastyleController::class,'auth500']);
Route::get('/auth404',[DastyleController::class,'auth404']);
Route::get('/authlock',[DastyleController::class,'authlock']);
Route::get('/register',[DastyleController::class,'register']);
Route::get('/authreverpw',[DastyleController::class,'authreverpw']);
Route::get('/index',[DastyleController::class,'indexD']);
Route::get('/widgets',[DastyleController::class,'widgetsD']);
Route::get('/uivideo',[DastyleController::class,'uivideo']);
Route::get('/chat',[DastyleController::class,'appschat']);
Route::get('/calendar',[DastyleController::class,'appscalendar']);
Route::get('/contact',[DastyleController::class,'appscontact']);
