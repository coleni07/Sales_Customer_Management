<?php

namespace Database\Seeders;

use App\Models\Workflow;
use Illuminate\Database\Seeder;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $workflows = [
            ['name' => 'Welcome Series', 'trigger' => 'New Signup', 'status' => 'active', 'action' => 'Send Email', 'audience' => 'New Leads', 'leads_enrolled' => 120],
            ['name' => 'Cart Abandonment', 'trigger' => 'Incomplete Checkout', 'status' => 'active', 'action' => 'Send SMS', 'audience' => 'Website Visitors', 'leads_enrolled' => 85],
            ['name' => 'Re-engagement', 'trigger' => 'Inactive 30 Days', 'status' => 'paused', 'action' => 'Send Email', 'audience' => 'Dormant Customers', 'leads_enrolled' => 47],
            ['name' => 'Birthday Offer', 'trigger' => 'Customer Birthday', 'status' => 'active', 'action' => 'Send Email', 'audience' => 'All Customers', 'leads_enrolled' => 63],
        ];

        foreach ($workflows as $w) {
            Workflow::create($w);
        }
    }
}