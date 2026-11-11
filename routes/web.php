<?php

use App\Http\Controllers\AdminITController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BackupFileController;
use App\Http\Controllers\BackupManagementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DatabaseOrangController;
use App\Http\Controllers\IntelUnitController;
use App\Http\Controllers\PimpinanFileManagerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\UnitDocumentController;
use App\Http\Controllers\UnitManagementController;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'home'])->name('home');
Route::view('/privacy', 'privacy')->name('privacy');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'processLogin'])
        ->middleware('throttle:login')
        ->name('login.process');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'session.fresh', 'role.unit.valid'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/photo', [ProfileController::class, 'photo'])
        ->middleware('throttle:media')
        ->name('profile.photo');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->middleware('throttle:sensitive-write')
        ->name('profile.update');

    Route::middleware('unit.isolation')->group(function () {
        Route::get('/data/{unit}', [IntelUnitController::class, 'index'])->name('units.index');
        Route::get('/data/{unit}/create', [IntelUnitController::class, 'create'])->name('units.create');
        Route::post('/data/{unit}', [IntelUnitController::class, 'store'])->middleware('throttle:sensitive-write')->name('units.store');
        Route::post('/data/{unit}/export-selected-pdf', [IntelUnitController::class, 'exportSelectedPdf'])->middleware('throttle:heavy-export')->name('units.export-selected-pdf');
        Route::get('/data/{unit}/report-pdf', [IntelUnitController::class, 'reportPdf'])->middleware('throttle:heavy-export')->name('units.report-pdf');
        Route::get('/data/{unit}/file-manager', [IntelUnitController::class, 'fileManager'])->name('units.file-manager');
        Route::get('/data/{unit}/attachments/{attachment}/preview', [IntelUnitController::class, 'previewAttachment'])->middleware('throttle:media')->name('units.attachments.preview');
        Route::get('/data/{unit}/attachments/{attachment}/download', [IntelUnitController::class, 'downloadAttachment'])->middleware('throttle:downloads')->name('units.attachments.download');
        Route::delete('/data/{unit}/attachments/{attachment}', [IntelUnitController::class, 'deleteAttachment'])->middleware('throttle:sensitive-write')->name('units.attachments.delete');
        Route::get('/data/{unit}/{id}/document', [UnitDocumentController::class, 'html'])->name('units.document');
        Route::get('/data/{unit}/{id}/pdf', [UnitDocumentController::class, 'pdf'])->middleware('throttle:heavy-export')->name('units.pdf');
        Route::get('/data/{unit}/{id}', [IntelUnitController::class, 'show'])->name('units.show');
        Route::get('/data/{unit}/{id}/edit', [IntelUnitController::class, 'edit'])->name('units.edit');
        Route::put('/data/{unit}/{id}', [IntelUnitController::class, 'update'])->middleware('throttle:sensitive-write')->name('units.update');
        Route::delete('/data/{unit}/{id}', [IntelUnitController::class, 'destroy'])->middleware('throttle:sensitive-write')->name('units.destroy');
    });

    Route::middleware('role:3,5')->group(function () {
        Route::get('/database-orang/export-pdf', [DatabaseOrangController::class, 'exportPdf'])->middleware('throttle:heavy-export')->name('database-orang.export-pdf');
        Route::post('/database-orang/import', [DatabaseOrangController::class, 'import'])->middleware('throttle:imports')->name('database-orang.import');
        Route::get('/database-orang/{database_orang}/pdf', [DatabaseOrangController::class, 'pdf'])->middleware('throttle:heavy-export')->name('database-orang.pdf');
        Route::get('/database-orang/{database_orang}/photo', [DatabaseOrangController::class, 'photo'])->middleware('throttle:media')->name('database-orang.photo');
        Route::get('/database-orang/{database_orang}/attachments/{attachment}/preview', [DatabaseOrangController::class, 'previewAttachment'])->middleware('throttle:media')->name('database-orang.attachments.preview');
        Route::get('/database-orang/{database_orang}/attachments/{attachment}/download', [DatabaseOrangController::class, 'downloadAttachment'])->middleware('throttle:downloads')->name('database-orang.attachments.download');
        Route::delete('/database-orang/{database_orang}/attachments/{attachment}', [DatabaseOrangController::class, 'deleteAttachment'])->middleware('throttle:sensitive-write')->name('database-orang.attachments.delete');
        Route::get('/database-orang', [DatabaseOrangController::class, 'index'])->name('database-orang.index');
        Route::get('/database-orang/create', [DatabaseOrangController::class, 'create'])->name('database-orang.create');
        Route::post('/database-orang', [DatabaseOrangController::class, 'store'])->middleware('throttle:sensitive-write')->name('database-orang.store');
        Route::get('/database-orang/{database_orang}', [DatabaseOrangController::class, 'show'])->name('database-orang.show');
        Route::get('/database-orang/{database_orang}/edit', [DatabaseOrangController::class, 'edit'])->name('database-orang.edit');
        Route::put('/database-orang/{database_orang}', [DatabaseOrangController::class, 'update'])->middleware('throttle:sensitive-write')->name('database-orang.update');
        Route::delete('/database-orang/{database_orang}', [DatabaseOrangController::class, 'destroy'])->middleware('throttle:sensitive-write')->name('database-orang.destroy');
    });

    Route::middleware('role:5')->group(function () {
        Route::view('/reports', 'reports.index')->name('reports.index');

        Route::prefix('pimpinan')->name('pimpinan.')->group(function () {
            Route::get('/file-manager', [PimpinanFileManagerController::class, 'index'])->name('file-manager');
            Route::get('/file-manager/export/{type}', [PimpinanFileManagerController::class, 'export'])->whereIn('type', ['pdf'])->middleware('throttle:heavy-export')->name('file-manager.export');
            Route::get('/file-manager/attachments/{attachment}/download', [PimpinanFileManagerController::class, 'downloadAttachment'])->middleware('throttle:downloads')->name('file-manager.attachments.download');
            Route::get('/file-manager/attachments/{attachment}/preview', [PimpinanFileManagerController::class, 'previewAttachment'])->middleware('throttle:media')->name('file-manager.attachments.preview');
            Route::delete('/file-manager/attachments/{attachment}', [PimpinanFileManagerController::class, 'deleteAttachment'])->middleware('throttle:sensitive-write')->name('file-manager.attachments.delete');
        });

        Route::prefix('admin-it')->name('admin.')->group(function () {
            Route::get('/users', [AdminITController::class, 'users'])->name('users');
            Route::post('/users', [AdminITController::class, 'storeUser'])->middleware('throttle:sensitive-write')->name('users.store');
            Route::patch('/users/{user}/toggle', [AdminITController::class, 'toggleUser'])->middleware('throttle:sensitive-write')->name('users.toggle');
            Route::delete('/users/{user}', [AdminITController::class, 'destroyUser'])->middleware('throttle:sensitive-write')->name('users.destroy');
            Route::get('/audit-trail', [AdminITController::class, 'auditTrail'])->name('audit');
            Route::get('/backup', [BackupManagementController::class, 'index'])->name('backup');
            Route::post('/backup', [BackupManagementController::class, 'trigger'])->middleware('throttle:backup')->name('backup.trigger');
            Route::get('/backup-files', [BackupFileController::class, 'index'])->name('backup.files');
            Route::get('/backup-files/download/{filename}', [BackupFileController::class, 'download'])->where('filename', '[A-Za-z0-9._-]+')->middleware('throttle:downloads')->name('backup.download');
            Route::delete('/backup-files/delete/{filename}', [BackupFileController::class, 'delete'])->where('filename', '[A-Za-z0-9._-]+')->middleware('throttle:sensitive-write')->name('backup.delete');
        });

        Route::prefix('super-admin')->name('super-admin.')->group(function () {
            Route::get('/', [SuperAdminController::class, 'index'])->name('index');
            Route::get('/units', [UnitManagementController::class, 'index'])->name('units.index');
            Route::get('/units/{unit}/edit', [UnitManagementController::class, 'edit'])->name('units.edit');
            Route::put('/units/{unit}', [UnitManagementController::class, 'update'])->middleware('throttle:sensitive-write')->name('units.update');
        });

    });
});
