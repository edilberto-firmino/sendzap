<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CampaignLogController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('contacts')->group(function () {
    Route::get('import', [ContactController::class, 'importForm'])->name('contacts.import.form');
    Route::post('import', [ContactController::class, 'import'])->name('contacts.import');
});

Route::resource('contacts', ContactController::class);

// Rotas de Campanhas
Route::prefix('campaigns')->group(function () {
    // Rotas de disparo
    Route::get('{campaign}/dispatch', [CampaignController::class, 'dispatchForm'])->name('campaigns.dispatch.form');
    Route::post('{campaign}/dispatch', [CampaignController::class, 'dispatch'])->name('campaigns.dispatch');
    
    // Rotas de relatórios
    Route::get('{campaign}/reports', [CampaignController::class, 'reports'])->name('campaigns.reports');
    
    // Rotas de logs
    Route::get('{campaign}/logs', [CampaignLogController::class, 'index'])->name('campaigns.logs.index');
    Route::get('logs/{log}', [CampaignLogController::class, 'show'])->name('campaigns.logs.show');
    Route::put('logs/{log}/status', [CampaignLogController::class, 'updateStatus'])->name('campaigns.logs.update-status');
});

Route::resource('campaigns', CampaignController::class);
