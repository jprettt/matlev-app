<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AtasanController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentPermissionController;
use App\Http\Controllers\MatlevController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'history'])->name('notifications.history');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

    Route::middleware('role:user')->group(function () {
        Route::get('/dashboard', [MatlevController::class, 'dashboard'])->name('user.dashboard');
        Route::get('/kriteria', [MatlevController::class, 'kriteria'])->name('user.kriteria');
        Route::get('/upload', [MatlevController::class, 'kriteria'])->name('user.upload');
        Route::get('/revisi', [MatlevController::class, 'revisi'])->name('user.revisi');
        Route::get('/riwayat', [MatlevController::class, 'riwayat'])->name('user.history');
        Route::get('/panduan', [MatlevController::class, 'panduan'])->name('user.panduan');
        Route::post('/upload/{maturityLevelId}', [MatlevController::class, 'upload'])->name('matlev.upload');
        Route::post('/evidence-requirements/{requirement}/upload', [MatlevController::class, 'uploadEvidenceRequirement'])->name('evidence.upload');
        Route::post('/evidence-slots/{slot}/upload', [MatlevController::class, 'uploadEvidenceSlot'])->name('evidence.slot.upload');
        Route::delete('/documents/revisions/{revision}', [DocumentPermissionController::class, 'destroyRevision'])->name('documents.revisions.delete');
        Route::post('/documents/{upload}/permission', [DocumentPermissionController::class, 'request'])->name('documents.permission.request');
        Route::delete('/documents/{upload}', [DocumentPermissionController::class, 'destroy'])->name('documents.delete');
        Route::post('/document-permissions/{permissionRequest}/respond', [DocumentPermissionController::class, 'respond'])->name('documents.permission.respond');
        Route::get('/export/bukti-terima', [MatlevController::class, 'exportReceipt'])->name('user.export.receipt');
        Route::get('/export/bukti-terima-pdf', [MatlevController::class, 'exportReceiptPdf'])->name('user.export.receipt.pdf');
    });

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/admin/queue', [AdminController::class, 'queue'])->name('admin.queue');
        Route::get('/admin/activity', [AdminController::class, 'activityHistory'])->name('admin.activity');
        Route::post('/admin/criteria', [AdminController::class, 'storeCriteria'])->name('admin.criteria.store');
        Route::delete('/admin/criteria/{id}', [AdminController::class, 'destroyCriteria'])->name('admin.criteria.delete');
        Route::post('/admin/subcriteria', [AdminController::class, 'storeSubcriteria'])->name('admin.subcriteria.store');
        Route::delete('/admin/subcriteria/{id}', [AdminController::class, 'destroySubcriteria'])->name('admin.subcriteria.delete');
        Route::post('/admin/maturity-levels', [AdminController::class, 'storeLevel'])->name('admin.level.store');
        Route::delete('/admin/maturity-levels/{id}', [AdminController::class, 'destroyLevel'])->name('admin.level.delete');
        Route::post('/admin/uploads/{id}/verify', [AdminController::class, 'verifyUpload'])->name('admin.verify');
    });

    Route::middleware('role:atasan')->group(function () {
        Route::get('/atasan/dashboard', [AtasanController::class, 'dashboard'])->name('atasan.dashboard');
        Route::get('/atasan/evidence', [AtasanController::class, 'approvedEvidence'])->name('atasan.evidence');
        Route::get('/atasan/status-summary', [AtasanController::class, 'statusSummary'])->name('atasan.status.summary');
        Route::get('/atasan/activity', [AtasanController::class, 'activityHistory'])->name('atasan.activity');
        Route::get('/atasan/users', [AtasanController::class, 'users'])->name('atasan.users');
        Route::post('/atasan/users', [AtasanController::class, 'storeUser'])->name('atasan.users.store');
        Route::put('/atasan/users/{user}', [AtasanController::class, 'updateUser'])->name('atasan.users.update');
        Route::patch('/atasan/users/{user}/toggle', [AtasanController::class, 'toggleUser'])->name('atasan.users.toggle');
        Route::get('/atasan/export', [AtasanController::class, 'exportForm'])->name('atasan.export');
        Route::get('/atasan/export-summary', [AtasanController::class, 'exportSummary'])->name('atasan.export.summary');
        Route::get('/atasan/export-pdf', [AtasanController::class, 'exportPdf'])->name('atasan.export.pdf');
        Route::get('/atasan/export-excel', [AtasanController::class, 'exportExcel'])->name('atasan.export.excel');
    });
});