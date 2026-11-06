<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->text('date_of_birth'); // encrypted cast
            $table->string('gender')->nullable();
            $table->text('ssn_last4'); // encrypted cast
            $table->string('npi', 10);
            $table->string('caqh_id')->nullable();
            $table->string('primary_email');
            $table->string('phone');
            $table->string('status')->default('pending');
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('practice_id')->constrained('practices')->restrictOnDelete();
            $table->string('primary_specialty')->nullable();
            $table->string('secondary_specialty')->nullable();
            $table->string('taxonomy_code')->nullable();
            $table->boolean('board_certified')->default(false);
            $table->string('board_name')->nullable();
            $table->date('certification_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->date('attested_at')->nullable();
            $table->date('attestation_due_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique('npi');
            $table->index('status');
            $table->index('attestation_due_at');
            $table->index('assigned_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
