<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NeoStatsController;
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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return view('neostats');
});

Route::post('/fetch-neostats', [NeoStatsController::class, 'fetchNeoStats'])->name('fetch.neostats');
