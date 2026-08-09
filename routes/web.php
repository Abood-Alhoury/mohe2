<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ApplicationsController;
use App\Http\Controllers\Admin\EditApplicationController;
use App\Http\Controllers\Admin\CommitteeController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\PdfReportController;
use App\Http\Controllers\Admin\DecisionsController;
use App\Http\Controllers\University\DashboardController as UniDashboardController;
use App\Http\Controllers\University\ApplicationWizardController;
use App\Http\Controllers\PageController;

// 1. Redirect Root based on authentication
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();
        if ($user->role && $user->role->name === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role && $user->role->name === 'university') {
            return redirect()->route('university.dashboard');
        }
    }
    return redirect()->route('login');
});

// Public Institutional Pages
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::post('/contact', [PageController::class, 'submitContact'])->name('contact.submit');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');

// 2. Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// 3. Admin Area (Protected by role:admin)
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Settings
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

    // Equivalence Applications & Messages Center
    Route::get('/applications', [ApplicationsController::class, 'index'])->name('admin.applications.index');
    Route::patch('/applications/{id}/status', [ApplicationsController::class, 'updateStatus'])->name('admin.applications.update_status');
    Route::post('/applications/{id}/message', [ApplicationsController::class, 'sendMessage'])->name('admin.applications.send_message');
    Route::get('/messages', [ApplicationsController::class, 'messagesLog'])->name('admin.messages.index');
    Route::get('/applications/{id}/edit', [EditApplicationController::class, 'edit'])->name('admin.applications.edit');
    Route::patch('/applications/{id}/candidate', [EditApplicationController::class, 'updateCandidate'])->name('admin.applications.update_candidate');
    Route::patch('/applications/{id}/education', [EditApplicationController::class, 'updateEducation'])->name('admin.applications.update_education');

    // General Committee Topics
    Route::get('/committee', [CommitteeController::class, 'index'])->name('admin.committee.index');
    Route::patch('/committee/{id}', [CommitteeController::class, 'decide'])->name('admin.committee.decide');

    // Advanced Search
    Route::get('/search', [SearchController::class, 'index'])->name('admin.search.index');

    // Reports & Statistics
    Route::get('/reports', [ReportsController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/export-pdf', [ReportsController::class, 'exportPdf'])->name('admin.reports.pdf');
    Route::get('/reports/{id}/mozhakkara', [PdfReportController::class, 'show'])->name('admin.reports.show');
    Route::get('/reports/{id}/pdf', [PdfReportController::class, 'downloadPdf'])->name('admin.reports.download_pdf');
    Route::get('/reports/{id}/consolidated', [PdfReportController::class, 'consolidatedView'])->name('admin.reports.consolidated');
    Route::get('/reports/{id}/consolidated-pdf', [PdfReportController::class, 'downloadConsolidatedPdf'])->name('admin.reports.download_consolidated_pdf');

    // Equivalence Decisions Upload & Issue
    Route::get('/decisions', [DecisionsController::class, 'index'])->name('admin.decisions.index');
    Route::post('/decisions', [DecisionsController::class, 'store'])->name('admin.decisions.store');
});

// 4. University Area (Protected by role:university)
Route::prefix('university')->middleware(['auth', 'role:university'])->group(function () {
    // Dashboard & Required Documents
    Route::get('/dashboard', [UniDashboardController::class, 'index'])->name('university.dashboard');
    Route::get('/required-documents', [UniDashboardController::class, 'requiredDocuments'])->name('university.required_documents');
    
    // Notifications & Messages
    Route::get('/messages', [UniDashboardController::class, 'messages'])->name('university.messages');
    Route::post('/applications/{appId}/reply', [UniDashboardController::class, 'replyMessage'])->name('university.applications.reply');
    Route::post('/applications/{appId}/nudge', [UniDashboardController::class, 'nudgeApplication'])->name('university.applications.nudge');

    // Wizard: Choose Equivalence Type
    Route::get('/apply/options', [ApplicationWizardController::class, 'showOptions'])->name('university.apply.options');
    
    // Wizard: Syrian Master's step-by-step
    Route::get('/apply/syrian-masters', [ApplicationWizardController::class, 'showSyrianMastersWizard'])->name('university.apply.syrian_masters');
    Route::post('/apply/syrian-masters', [ApplicationWizardController::class, 'submitSyrianMastersWizard'])->name('university.apply.syrian_masters.submit');
    
    // Candidate Lookup API (for second-time equivalence auto-fill)
    Route::get('/candidate/lookup', [ApplicationWizardController::class, 'lookupCandidate'])->name('university.candidate.lookup');

    // View, Edit, Update & Delete Application / Draft
    Route::get('/applications/{appId}/show', [UniDashboardController::class, 'showApplication'])->name('university.applications.show');
    Route::get('/applications/{appId}/edit', [UniDashboardController::class, 'editApplication'])->name('university.applications.edit');
    Route::post('/applications/{appId}/update', [UniDashboardController::class, 'updateApplication'])->name('university.applications.update');
    Route::delete('/applications/{appId}/draft', [UniDashboardController::class, 'deleteDraft'])->name('university.applications.delete_draft');

    // Official Application Report & Mozhakkara PDF Download
    Route::get('/applications/{appId}/mozhakkara', [UniDashboardController::class, 'showMozhakkara'])->name('university.applications.mozhakkara');
    Route::get('/applications/{appId}/download-pdf', [UniDashboardController::class, 'downloadPdf'])->name('university.applications.download_pdf');
    Route::get('/applications/{appId}/consolidated-pdf', [UniDashboardController::class, 'downloadConsolidatedPdf'])->name('university.applications.download_consolidated_pdf');
});
