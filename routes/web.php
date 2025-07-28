<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('contacts')->group(function () {
    Route::get('import', [ContactController::class, 'importForm'])->name('contacts.import.form');
    Route::post('import', [ContactController::class, 'import'])->name('contacts.import');
});

Route::resource('contacts', ContactController::class);
