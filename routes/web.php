<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ApplicationsController;
use App\Http\Controllers\Admin\EditApplicationController;
use App\Http\Controllers\Admin\CommitteeController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\PdfReportController;
use App\Http\Controllers\Admin\DecisionsController;

// Redirect root to Admin Dashboard
Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::prefix('admin')->group(function () {
    // 1. Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // 2. Settings (Tabs: Add Admin, Add Uni, Uni Accounts, Add Country, Add Level, Site Lock)
    Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings');
    Route::post('/settings/admin', [SettingsController::class, 'storeAdmin'])->name('admin.settings.store_admin');
    Route::post('/settings/university', [SettingsController::class, 'storeUniversity'])->name('admin.settings.store_university');
    Route::post('/settings/uni-account', [SettingsController::class, 'storeUniAccount'])->name('admin.settings.store_uni_account');
    Route::patch('/settings/uni-status/{id}', [SettingsController::class, 'updateUniStatus'])->name('admin.settings.update_uni_status');
    Route::post('/settings/country', [SettingsController::class, 'storeCountry'])->name('admin.settings.store_country');
    Route::post('/settings/level', [SettingsController::class, 'storeEducationLevel'])->name('admin.settings.store_level');
    Route::post('/settings/site-lock', [SettingsController::class, 'toggleSiteLock'])->name('admin.settings.toggle_site_lock');
    Route::delete('/settings/user/{id}', [SettingsController::class, 'deleteUser'])->name('admin.settings.delete_user');
    Route::delete('/settings/university/{id}', [SettingsController::class, 'deleteUniversity'])->name('admin.settings.delete_university');
    Route::delete('/settings/country/{id}', [SettingsController::class, 'deleteCountry'])->name('admin.settings.delete_country');
    Route::delete('/settings/level/{id}', [SettingsController::class, 'deleteEducationLevel'])->name('admin.settings.delete_level');

    // 3. Equivalence Applications
    Route::get('/applications', [ApplicationsController::class, 'index'])->name('admin.applications.index');
    Route::patch('/applications/{id}/status', [ApplicationsController::class, 'updateStatus'])->name('admin.applications.update_status');
    Route::post('/applications/{id}/message', [ApplicationsController::class, 'sendMessage'])->name('admin.applications.send_message');
    Route::get('/applications/{id}/edit', [EditApplicationController::class, 'edit'])->name('admin.applications.edit');
    Route::patch('/applications/{id}/candidate', [EditApplicationController::class, 'updateCandidate'])->name('admin.applications.update_candidate');
    Route::patch('/applications/{id}/education', [EditApplicationController::class, 'updateEducation'])->name('admin.applications.update_education');

    // 4. General Committee Topics
    Route::get('/committee', [CommitteeController::class, 'index'])->name('admin.committee.index');
    Route::patch('/committee/{id}', [CommitteeController::class, 'decide'])->name('admin.committee.decide');

    // 5. Advanced Search
    Route::get('/search', [SearchController::class, 'index'])->name('admin.search.index');

    // 6. Reports & Statistics
    Route::get('/reports', [ReportsController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/{id}/mozhakkara', [PdfReportController::class, 'show'])->name('admin.reports.show');
    Route::get('/reports/{id}/pdf', [PdfReportController::class, 'downloadPdf'])->name('admin.reports.download_pdf');
    Route::get('/reports/{id}/consolidated', [PdfReportController::class, 'consolidatedView'])->name('admin.reports.consolidated');

    // 7. Equivalence Decisions Upload & Issue
    Route::get('/decisions', [DecisionsController::class, 'index'])->name('admin.decisions.index');
    Route::post('/decisions', [DecisionsController::class, 'store'])->name('admin.decisions.store');
});
