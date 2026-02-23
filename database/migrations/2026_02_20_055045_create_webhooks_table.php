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
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('source_name');
            $table->string('endpoint_key');
            $table->text('endpoint_url');
            $table->boolean('is_active')->default(true);
            $table->json('headers')->nullable();
            $table->text('secret')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['tenant_id']);
            $table->index(['source_name']);
            $table->index(['endpoint_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhooks');
    }
};
