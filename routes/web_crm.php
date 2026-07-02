<?php
use App\Http\Controllers\Crm\ContactController;
use App\Http\Controllers\Crm\EmailBlastController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'role:' . \App\Constants\Roles::SUPER_ADMIN . ',' . \App\Constants\Roles::MASTER_ADMIN . ',' . \App\Constants\Roles::SALES])
    ->prefix('crm')
    ->name('crm.')
    ->group(function () {
        // Menu Contacts
        Route::resource('contacts', ContactController::class);
        Route::resource('email-blasts', EmailBlastController::class)->only([
            'index',
            'create',
            'store',
            'show',
            'destroy',
        ]);
    });
