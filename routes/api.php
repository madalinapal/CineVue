<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AccountApiController;
use App\Http\Controllers\Api\TicketApiController;
use App\Http\Controllers\Api\ProjectionApiController;
use App\Http\Controllers\Api\MovieApiController;

use App\Http\Controllers\Api\AdminMovieController;
use App\Http\Controllers\Api\StatsApiController;      // top-movies
use App\Http\Controllers\Api\StatsController;         // tickets-by-client-type

Route::get('/stats/top-movies', [StatsApiController::class, 'topMovies']);
Route::get('/stats/tickets-by-client-type', [StatsController::class, 'ticketsByClientType']);
Route::get('/stats/top-halls-occupancy', [StatsController::class, 'topHallsOccupancy']);
Route::get('/stats/projections-high-occupancy', [StatsController::class, 'projectionsHighOccupancy']);
Route::get('/stats/top-users', [StatsController::class, 'topUsers']);
Route::get('/stats/soldout-movies', [StatsApiController::class, 'soldOutMovies']);


Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/movies', [AdminMovieController::class, 'index']);
    Route::post('/movies', [AdminMovieController::class, 'store']);
    Route::put('/movies/{id}', [AdminMovieController::class, 'update']);
    Route::delete('/movies/{id}', [AdminMovieController::class, 'destroy']);

    Route::get('/sali', [AdminMovieController::class, 'sali']);
    Route::get('/movies/{id}/projections', [AdminMovieController::class, 'projections']);
});

Route::get('/movies', [MovieApiController::class, 'index']);
Route::get('/movies/search', [MovieApiController::class, 'search']); 


Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('/projections', [ProjectionApiController::class, 'index']);
Route::get('/projections/{id}/occupied-seats', [ProjectionApiController::class, 'occupiedSeats']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/account/{user}', [AccountApiController::class, 'show']);
    Route::put('/account/{user}', [AccountApiController::class, 'update']);
    Route::post('/tickets', [TicketApiController::class, 'store']);
});
