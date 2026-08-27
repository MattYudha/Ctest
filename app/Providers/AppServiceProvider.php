<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;
use App\Models\OvertimeSubmission;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register QrCode facade alias
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('QrCode', \SimpleSoftwareIO\QrCode\Facades\QrCode::class);

        // Register Repositories
        $this->app->bind(
            \App\Repositories\Contracts\FaceRepositoryInterface::class,
            \App\Repositories\FaceRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Register missing signature verification route as a fallback since routes/web.php is read-only
        \Illuminate\Support\Facades\Route::middleware(['web'])
            ->get('verify-signature', [\App\Http\Controllers\SignatureController::class, 'publicVerify'])
            ->name('signatures.public-verify');

        // Fix for route collision: specific POST route for verification
        \Illuminate\Support\Facades\Route::middleware(['web', 'auth', 'role:HR Administrator,' . \App\Constants\Roles::MASTER_ADMIN])
            ->post('signature-approve/{signature}', [\App\Http\Controllers\SignatureController::class, 'verify'])
            ->name('signatures.verify.fixed');

        if (Schema::hasTable('settings')) {
            
            // Timpa nilai konfigurasi statis dengan nilai dari database
            config([
                'payroll.bpjs_kes_employee_rate'   => (float) Setting::getValue('bpjs_kes_employee_rate', 0.01),
                'payroll.bpjs_tk_employee_rate'    => (float) Setting::getValue('bpjs_tk_employee_rate', 0.02),
                'payroll.default_working_days'     => (int) Setting::getValue('default_working_days', 22),
                'payroll.working_hours_per_day'    => (int) Setting::getValue('working_hours_per_day', 8),
                'payroll.overtime_rate_per_hour' => (float) Setting::getValue('overtime_rate_per_hour', 1.5),
            ]);
            
        }
    }
}
