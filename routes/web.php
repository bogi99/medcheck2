
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MedcheckController;

Route::get('/', [MedcheckController::class, 'index'])->name('schedule');
Route::post('/take/{id}', [MedcheckController::class, 'take'])->name('take');
Route::post('/reset', [MedcheckController::class, 'reset'])->name('reset');

Route::get('/setup', [MedcheckController::class, 'setup'])->name('setup');
Route::post('/add-pill', [MedcheckController::class, 'addPill'])->name('addPill');
Route::post('/delete-pill/{id}', [MedcheckController::class, 'deletePill'])->name('deletePill');
Route::post('/edit-pill/{id}', [MedcheckController::class, 'editPill'])->name('editPill');
