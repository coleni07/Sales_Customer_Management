<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('objective');
            $table->text('description')->nullable();

            $table->string('channel');
            $table->string('audience');
            $table->string('subject_line');
            $table->text('message');
            $table->string('media_path')->nullable();

            $table->date('send_date');
            $table->time('send_time')->nullable();
            $table->string('timezone')->nullable();

            $table->enum('status', ['draft', 'scheduled'])->default('draft');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
