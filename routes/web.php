<?php

use App\Http\Controllers\FilmController;
use App\Http\Controllers\HallController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\SeanceController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

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


// Route::get('/', [ MainController::class, 'home' ]);

// Получение всех данных
Route::get('/alldata', [ MainController::class, 'getData' ]);
// Работа с залами
Route::post('/hall', [ HallController::class, 'hallAdd' ]);
Route::delete('/hall/{id}', [ HallController::class, 'hallDel' ]);
Route::post('/hall/{id}', [ HallController::class, 'hallConfig' ]);
Route::post('/price/{id}', [ HallController::class, 'setPrice' ]);
Route::post('/open/{id}', [ HallController::class, 'OpenHall' ]);
// Работа с фильмами
Route::post('/film', [ FilmController::class, 'filmAdd' ]);
Route::delete('/film/{id}', [ FilmController::class, 'filmDell' ]);
// Работа с сеансами
Route::post('/seance', [ SeanceController::class, 'seanceAdd' ]);
Route::delete('/seance/{id}', [ SeanceController::class, 'seanceDell' ]);
// Работа с билетами
Route::post('/ticket', [ TicketController::class, 'ticketAdd' ]);
// Получение актуальной схемы зала на выбранный сеанс с учетом даты и время
Route::get('/hallconfig', [ MainController::class, 'getHallConfig' ]);
// Регистрация
Route::post('/login', [ MainController::class, 'login' ]);







// Route::get('/about', [ MainController::class, 'about']);

// Route::get('/review', [ MainController::class, 'review'])->name('review');
// Route::post('/review/check', [ MainController::class, 'review_chek']);

// Route::get('/', function () {
    //return 'ID: ' . $id . ' Name: ' . $name;
   // return 'Привет';
 //});
