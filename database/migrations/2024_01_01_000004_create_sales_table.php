<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->foreignId('representative_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('amount', 10, 2);
            $table->date('sale_date');
            $table->timestamps();

            // These columns are queried constantly (grouping by date, product, region, rep)
            // so indexing them keeps the report page fast as the table grows.
            $table->index('sale_date');
            $table->index('product_id');
            $table->index('region_id');
            $table->index('representative_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
