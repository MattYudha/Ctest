<?php
use App\Http\Controllers\Crm\ContactController;
use App\Http\Controllers\Crm\EmailBlastController;
use App\Http\Controllers\Crm\CrmDashboardController;
use App\Http\Controllers\Crm\CrmBoardController;
use App\Http\Controllers\Crm\EmailInboxController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:' . \App\Constants\Roles::SUPER_ADMIN . ',' . \App\Constants\Roles::MASTER_ADMIN . ',' . \App\Constants\Roles::SALES])
    ->prefix('crm')
    ->name('crm.')
    ->group(function () {
        Route::get('dashboard', [CrmDashboardController::class, 'index'])->name('dashboard');
        
        Route::get('board', [CrmBoardController::class, 'index'])->name('board.index');
        Route::post('board', [CrmBoardController::class, 'store'])->name('board.store');
        Route::put('board/{id}', [CrmBoardController::class, 'update'])->name('board.update');
        Route::post('board/update-status', [CrmBoardController::class, 'updateStatus'])->name('board.update-status');
        Route::delete('board/{id}', [CrmBoardController::class, 'destroy'])->name('board.destroy');

        // Menu Contacts
        Route::resource('contacts', ContactController::class);
        
        // CRM Message Templates
        Route::resource('templates', \App\Http\Controllers\Crm\CrmTemplateController::class);
        Route::resource('email-blasts', EmailBlastController::class)->only([
            'index',
            'create',
            'store',
            'show',
            'destroy',
        ]);
        
        Route::post('email-blasts/{id}/mark-sent', [EmailBlastController::class, 'markAsSent'])->name('email-blasts.mark-sent');

        Route::resource('wa-blasts', \App\Http\Controllers\Crm\WaBlastController::class)->only([
            'index',
            'create',
            'store',
            'show',
            'destroy',
        ]);
        Route::post('wa-blasts/{id}/mark-sent', [\App\Http\Controllers\Crm\WaBlastController::class, 'markAsSent'])->name('wa-blasts.mark-sent');
    
        Route::resource('email-inbox', EmailInboxController::class)->only(['index']);
    });
