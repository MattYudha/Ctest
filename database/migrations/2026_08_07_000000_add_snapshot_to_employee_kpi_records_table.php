<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('employee_kpi_records', 'kpi_config_snapshot')) {
            Schema::table('employee_kpi_records', function (Blueprint $table) {
                $table->json('kpi_config_snapshot')->nullable()->after('composite_score');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_kpi_records', function (Blueprint $table) {
            $table->dropColumn('kpi_config_snapshot');
        });
    }
};
