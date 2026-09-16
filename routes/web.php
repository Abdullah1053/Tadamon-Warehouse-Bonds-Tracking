<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BondController;
use App\Http\Controllers\StackController;

// Dashboard (The single entry point)
Route::get('/', [StackController::class, 'index'])->name('dashboard');

// Bond Entry
Route::get('/bonds/create', [BondController::class, 'create'])->name('bonds.create');
Route::post('/bonds/store', [BondController::class, 'store'])->name('bonds.store');
Route::get('/bonds', [BondController::class, 'index'])->name('bonds.index');
Route::post('/bonds/bulk-assign', [BondController::class, 'bulkAssign'])->name('bonds.bulkAssign');
Route::post('/bonds/{bond}/detach', [BondController::class, 'detach'])->name('bonds.detach');
Route::delete('/bonds/{bond}', [BondController::class, 'destroy'])->name('bonds.destroy');
Route::get('/bonds/{bond}', [BondController::class, 'show'])->name('bonds.show');
Route::get('/bonds/{bond}/edit', [BondController::class, 'edit'])->name('bonds.edit');
Route::put('/bonds/{bond}', [BondController::class, 'update'])->name('bonds.update');


// Stack & Export Actions
Route::post('/stacks/create', [StackController::class, 'createStack'])->name('stacks.create');
Route::post('/stacks/bulk-assign', [StackController::class, 'bulkAssign'])->name('stacks.bulkAssign');
Route::get('/stacks/export-all', [StackController::class, 'exportAll'])->name('stacks.exportAll');
Route::get('/stacks/export/{stack}', [StackController::class, 'export'])->name('stacks.export');