<?php

namespace Database\Seeders;

use App\Models\SupportTicket;
use Illuminate\Database\Seeder;

class SupportTicketSeeder extends Seeder
{
    public function run(): void
    {
        $tickets = [
            ['customer_name' => 'Juan Dela Cruz', 'subject' => 'Order arrived damaged', 'priority' => 'High', 'status' => 'Open', 'assigned_to' => 'Kyle Anthony', 'description' => 'Customer received a Marshall speaker with a cracked casing and is requesting a replacement.'],
            ['customer_name' => 'Maria Santos', 'subject' => 'Delayed shipment', 'priority' => 'Medium', 'status' => 'In Progress', 'assigned_to' => 'Kyle Anthony', 'description' => 'Order SO-1002 has not moved past "processing" for 4 days; customer is asking for an update.'],
            ['customer_name' => 'Kevin Reyes', 'subject' => 'Wrong item shipped', 'priority' => 'High', 'status' => 'Open', 'assigned_to' => 'Dana Ruiz', 'description' => 'Customer ordered a Fast Charger but received a USB-C Cable instead.'],
            ['customer_name' => 'Ana Garcia', 'subject' => 'Refund request', 'priority' => 'Medium', 'status' => 'Done', 'assigned_to' => 'Dana Ruiz', 'description' => 'Customer cancelled order before shipping and refund has been processed.'],
            ['customer_name' => 'Luiz Mendoza', 'subject' => 'Question about warranty', 'priority' => 'Low', 'status' => 'Open', 'assigned_to' => 'Kyle Anthony', 'description' => 'Customer wants to confirm warranty coverage period for the Bluetooth Speaker.'],
            ['customer_name' => 'Sofie Lopez', 'subject' => 'Discount code not applying', 'priority' => 'Medium', 'status' => 'In Progress', 'assigned_to' => 'Dana Ruiz', 'description' => 'Corporate discount code returns an error at checkout for a bulk order.'],
            ['customer_name' => 'Eloise Briderton', 'subject' => 'Account login issue', 'priority' => 'Low', 'status' => 'Done', 'assigned_to' => 'Kyle Anthony', 'description' => 'Customer could not log in; password reset email resolved the issue.'],
        ];

        foreach ($tickets as $ticket) {
            SupportTicket::create($ticket);
        }
    }
}
