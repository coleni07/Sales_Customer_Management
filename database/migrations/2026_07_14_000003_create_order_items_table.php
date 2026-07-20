<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('product_name');
            $table->string('store_name');
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->string('icon')->default('fa-box'); // Font Awesome icon class used instead of a real photo
            $table->string('status')->default('pending'); // delivered | cancelled | pending
            $table->date('expected_delivery');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
