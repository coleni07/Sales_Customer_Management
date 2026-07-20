<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Note: this is a separate "support_tickets" table, distinct from the
    // existing "tickets" table used by the Dashboard's "Latest Tickets"
    // widget (App\Models\Ticket). The Support System sub-module has its
    // own ticket shape (priority, assigned_to, free-text customer name),
    // so it gets its own table + model (SupportTicket) to avoid clashing
    // with the existing Ticket model/schema.
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('subject');
            $table->text('description');
            $table->enum('priority', ['High', 'Medium', 'Low'])->default('Medium');
            $table->enum('status', ['Open', 'In Progress', 'Done'])->default('Open');
            $table->string('assigned_to')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
