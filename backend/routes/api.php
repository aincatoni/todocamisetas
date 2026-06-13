<?php

use App\Http\Controllers\CamisetaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\TallaController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::get('camisetas', [CamisetaController::class, 'index']);
Route::post('camisetas', [CamisetaController::class, 'store']);
Route::get('camisetas/{id}', [CamisetaController::class, 'show']);
Route::put('camisetas/{id}', [CamisetaController::class, 'update']);
Route::delete('camisetas/{id}', [CamisetaController::class, 'destroy']);

Route::get('clientes', [ClienteController::class, 'index']);
Route::post('clientes', [ClienteController::class, 'store']);
Route::get('clientes/{id}', [ClienteController::class, 'show']);
Route::get('clientes/{id}/camisetas', [ClienteController::class, 'camisetas']);
Route::put('clientes/{id}', [ClienteController::class, 'update']);
Route::delete('clientes/{id}', [ClienteController::class, 'destroy']);

Route::get('tallas', [TallaController::class, 'index']);
Route::post('tallas', [TallaController::class, 'store']);
Route::get('tallas/{id}', [TallaController::class, 'show']);
Route::put('tallas/{id}', [TallaController::class, 'update']);
Route::delete('tallas/{id}', [TallaController::class, 'destroy']);

Route::get('camisetas/{id}/tallas', [TallaController::class, 'listByCamiseta']);
Route::post('camisetas/{id}/tallas', [TallaController::class, 'attachToCamiseta']);
Route::delete('camisetas/{id}/tallas/{tallaId}', [TallaController::class, 'detachFromCamiseta']);
