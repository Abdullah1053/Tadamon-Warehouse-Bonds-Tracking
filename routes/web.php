<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BondController;
use App\Http\Controllers\StackController;
use App\Http\Controllers\ReceiverNormalizationController;

// Dashboard (The single entry point)
Route::get('/', [StackController::class, 'index'])->name('dashboard');

// Bond Entry
Route::get('/bonds/create', [BondController::class, 'create'])->name('bonds.create');
Route::post('/bonds/store', [BondController::class, 'store'])->name('bonds.store');
Route::get('/bonds', [BondController::class, 'index'])->name('bonds.index');
Route::post('/bonds/bulk-assign', [BondController::class, 'bulkAssign'])->name('bonds.bulkAssign');
Route::post('/bonds/bulk-action', [BondController::class, 'bulkAction'])->name('bonds.bulkAction');
Route::get('/bonds/upload-images', [BondController::class, 'bulkUploadView'])->name('bonds.uploadImagesView');
Route::post('/bonds/upload-images', [BondController::class, 'bulkUpload'])->name('bonds.bulkUpload');
Route::post('/bonds/{bond}/detach', [BondController::class, 'detach'])->name('bonds.detach');
Route::delete('/bonds/{bond}', [BondController::class, 'destroy'])->name('bonds.destroy');
Route::get('/bonds/{bond}', [BondController::class, 'show'])->name('bonds.show');
Route::get('/bonds/{bond}/edit', [BondController::class, 'edit'])->name('bonds.edit');
Route::put('/bonds/{bond}', [BondController::class, 'update'])->name('bonds.update');


// Stack & Export Actions
Route::get('/stacks/{stack}', [StackController::class, 'show'])->name('stacks.show');
Route::post('/stacks/create', [StackController::class, 'createStack'])->name('stacks.create');
Route::post('/stacks/bulk-assign', [StackController::class, 'bulkAssign'])->name('stacks.bulkAssign');
Route::get('/stacks/export-all', [StackController::class, 'exportAll'])->name('stacks.exportAll');
Route::get('/stacks/export-all-excel', [StackController::class, 'exportAllSingleExcel'])->name('stacks.exportAllExcel');
Route::get('/stacks/export/{stack}', [StackController::class, 'export'])->name('stacks.export');

// Receiver Normalization & Deduplication
Route::get('/receivers/normalize', [ReceiverNormalizationController::class, 'index'])->name('receivers.normalize');
Route::get('/receivers/api/clusters', [ReceiverNormalizationController::class, 'clustersApi'])->name('receivers.api.clusters');
Route::get('/receivers/api/preview', [ReceiverNormalizationController::class, 'preview'])->name('receivers.api.preview');
Route::post('/receivers/merge', [ReceiverNormalizationController::class, 'merge'])->name('receivers.merge');
Route::post('/receivers/merge-custom', [ReceiverNormalizationController::class, 'mergeCustom'])->name('receivers.mergeCustom');