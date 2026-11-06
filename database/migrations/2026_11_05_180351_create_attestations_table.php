<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attestations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->date('attested_at')->nullable();
            $table->date('due_at');
            $table->foreignId('attested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('current');
            $table->timestamps();

            $table->index('status');
            $table->index('due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attestations');
    }
};
