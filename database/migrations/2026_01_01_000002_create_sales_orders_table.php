<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique(); // SO-1001
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->string('discount_label')->nullable(); // e.g. "10% Corp"
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('tax_label')->nullable(); // e.g. "VAT 12%"
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('shipping_fee', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0); // grand total
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['cod', 'credit', 'debit'])->default('cod');
            $table->enum('approval_status', ['approved', 'unapproved'])->default('unapproved');
            $table->string('warehouse_code')->nullable(); // e.g. W102
            $table->string('gl_code')->nullable(); // e.g. GL-201
            $table->date('order_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
