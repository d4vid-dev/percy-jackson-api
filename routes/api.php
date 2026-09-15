<?php

use App\Http\Controllers\PersonagemController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/personagens', [PersonagemController::class, 'index']);
Route::get('/personagens/{personagem}', [PersonagemController::class, 'show']);
Route::post('/personagens', [PersonagemController::class, 'store']);
Route::put('/personagens/{personagem}', [PersonagemController::class, 'update']);
Route::patch('/personagens/{personagem}', [PersonagemController::class, 'update']);
Route::delete('/personagens/{personagem}', [PersonagemController::class, 'destroy']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
