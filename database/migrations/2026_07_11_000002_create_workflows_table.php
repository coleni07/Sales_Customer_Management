<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger');
            $table->enum('status', ['active', 'paused'])->default('active');
            $table->string('action');
            $table->string('audience')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('leads_enrolled')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
