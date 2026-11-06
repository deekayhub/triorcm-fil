<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('payer_id')->constrained('payers')->cascadeOnDelete();
            $table->string('stage')->default('intake');
            $table->date('submitted_at')->nullable();
            $table->date('approved_at')->nullable();
            $table->date('effective_date')->nullable();
            $table->string('reference_no')->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('stage');
            $table->index(['provider_id', 'payer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
