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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable();
            
            // Domain Info
            $table->string('name')->unique();
            $table->string('registrar')->nullable();
            $table->string('registrar_account')->nullable();
            $table->text('nameservers')->nullable();
            
            // Dates
            $table->date('registered_date')->nullable();
            $table->date('expiry_date');
            $table->date('renewal_date')->nullable();
            
            // Renewal
            $table->boolean('auto_renewal')->default(false);
            $table->decimal('annual_cost', 10, 2)->nullable();
            
            // Status
            $table->enum('status', ['active', 'expired', 'expiring', 'renewal_pending'])
                ->default('active');
            $table->boolean('is_critical')->default(false);
            
            // Notes
            $table->text('notes')->nullable();
            
            // Tracking
            $table->unsignedBigInteger('created_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('client_id');
            $table->index('name');
            $table->index('expiry_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};