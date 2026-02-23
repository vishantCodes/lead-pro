<?php

use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadNoteController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CommissionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Leads routes
    Route::resource('leads', LeadController::class);
    Route::post('leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
    Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert');
    Route::post('leads/{lead}/notes', [LeadNoteController::class, 'store'])->name('leads.notes.store');
    Route::delete('leads/{lead}/notes/{note}', [LeadNoteController::class, 'destroy'])->name('leads.notes.destroy');
    
    // Campaigns routes
    Route::resource('campaigns', CampaignController::class);
    
    // Commissions routes
    Route::get('commissions', [CommissionController::class, 'index'])->name('commissions.index');
    Route::post('commissions/{commission}/approve', [CommissionController::class, 'approve'])->name('commissions.approve');
    Route::post('commissions/{commission}/reject', [CommissionController::class, 'reject'])->name('commissions.reject');
    Route::post('commissions/{commission}/pay', [CommissionController::class, 'pay'])->name('commissions.pay');
});

require __DIR__.'/settings.php';
