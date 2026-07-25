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
            ['customer_name' => 'Eloise Briderton', 'subject' => 'Account login issue', 'priority' => 'Low', 'status' => 'Done', 'assigned_to' => 'Kyle Anthony', 'description' => 'Customer could not log in, password reset email resolved the issue.'],
            ['customer_name' => 'Carlo Villanueva', 'subject' => 'Missing item in order', 'priority' => 'High', 'status' => 'Open', 'assigned_to' => 'Dana Ruiz', 'description' => 'Order SO-1010 arrived with only 1 of the 2 Power Banks that were ordered; customer wants the missing unit shipped.'],
            ['customer_name' => 'Bianca Torres', 'subject' => 'Tracking number not working', 'priority' => 'Medium', 'status' => 'Open', 'assigned_to' => 'Kyle Anthony', 'description' => 'The courier tracking link for the customer\'s order returns "not found"; needs a valid tracking reference.'],
            ['customer_name' => 'Miguel Ramos', 'subject' => 'Requesting invoice copy', 'priority' => 'Low', 'status' => 'In Progress', 'assigned_to' => 'Dana Ruiz', 'description' => 'Customer needs an official copy of the invoice for order SO-1005 for expense reimbursement.'],
            ['customer_name' => 'Patricia Cruz', 'subject' => 'Product not as described', 'priority' => 'Medium', 'status' => 'Open', 'assigned_to' => 'Kyle Anthony', 'description' => 'Customer says the Bluetooth Speaker received does not match the color shown on the product listing.'],
            ['customer_name' => 'Renzo Aquino', 'subject' => 'Payment charged twice', 'priority' => 'High', 'status' => 'In Progress', 'assigned_to' => 'Dana Ruiz', 'description' => 'Customer\'s card was charged twice for the same order; needs one charge reversed.'],
            ['customer_name' => 'Camille Fernandez', 'subject' => 'How to change delivery address', 'priority' => 'Low', 'status' => 'Done', 'assigned_to' => 'Kyle Anthony', 'description' => 'Customer asked how to update the shipping address before dispatch; instructions were provided.'],
            ['customer_name' => 'Diego Salazar', 'subject' => 'Item defective on arrival', 'priority' => 'High', 'status' => 'Open', 'assigned_to' => 'Dana Ruiz', 'description' => 'The Mechanical Keyboard arrived with several unresponsive keys; customer is requesting a replacement unit.'],
            ['customer_name' => 'Jasmine Ocampo', 'subject' => 'Cancel order request', 'priority' => 'Medium', 'status' => 'Open', 'assigned_to' => 'Kyle Anthony', 'description' => 'Customer wants to cancel order SO-1006 before it ships and requests confirmation once processed.'],
            ['customer_name' => 'Nathaniel Bautista', 'subject' => 'Loyalty points not credited', 'priority' => 'Low', 'status' => 'In Progress', 'assigned_to' => 'Dana Ruiz', 'description' => 'Customer\'s recent purchase did not add loyalty points to their account as expected.'],
            ['customer_name' => 'Isabel Navarro', 'subject' => 'Bulk order inquiry', 'priority' => 'Medium', 'status' => 'Done', 'assigned_to' => 'Kyle Anthony', 'description' => 'Customer asked about bulk pricing for 50+ units; pricing sheet was sent and inquiry resolved.'],
        ];

        foreach ($tickets as $ticket) {
            SupportTicket::create($ticket);
        }
    }
}
