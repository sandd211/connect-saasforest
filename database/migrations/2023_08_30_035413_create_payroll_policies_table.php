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
        Schema::create('payroll_policies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('policy_from');
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('tax_slab_id')->constrained()->onDelete('cascade');
            $table->foreignId('over_time_rate_id')->constrained()->onDelete('cascade');
            $table->foreignId('social_security_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->nullable();
            $table->foreignId('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_policies');
    }
};
