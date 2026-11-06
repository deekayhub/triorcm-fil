<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->string('type');
            $table->text('number'); // encrypted cast (required for DEA)
            $table->string('state', 2)->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index(['provider_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_licenses');
    }
};
