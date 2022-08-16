<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Route::post('product/sendToSite', 'ProductController@sendToSite');
Route::group(['middleware' => 'auth'], function(){
    Route::post('/products/sendToSite', [ProductController::class, 'sendToSite'])->name('products.sendToSite');
    Route::resource('products',ProductController::class);
});

require __DIR__.'/auth.php';
