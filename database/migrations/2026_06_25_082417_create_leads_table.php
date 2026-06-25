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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('country')->nullable();
            
            // Lead Source
            $table->enum('source', ['website', 'facebook', 'google', 'referral', 'whatsapp', 'other'])
                ->default('website');
            
            // Lead Status
            $table->enum('status', ['new', 'contacted', 'proposal_sent', 'won', 'lost'])
                ->default('new');
            
            // Assignment
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('converted_to_client_id')->nullable();
            
            // Additional
            $table->text('notes')->nullable();
            $table->decimal('estimated_value', 12, 2)->nullable();
            $table->date('conversion_date')->nullable();
            
            // Tracking
            $table->unsignedBigInteger('created_by')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index('email');
            $table->index('status');
            $table->index('source');
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};