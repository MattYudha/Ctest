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
        Schema::create('crm_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['email', 'wa'])->default('wa');
            $table->string('subject')->nullable(); // For email only
            $table->text('body');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crm_templates');
    }
};
