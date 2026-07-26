<?php

namespace Database\Seeders;

use App\Models\Campaign;
use Illuminate\Database\Seeder;

class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $campaigns = [
            ['name' => 'Summer Sale Blast', 'type' => 'Email', 'objective' => 'Sales', 'channel' => 'Email', 'audience' => 'All Customers', 'subject_line' => 'Summer Sale is here!', 'message' => 'Enjoy up to 30% off this summer.', 'send_date' => '2026-05-07', 'status' => 'scheduled'],
            ['name' => 'Weekend Flashsale', 'type' => 'SMS', 'objective' => 'Sales', 'channel' => 'TikTok', 'audience' => 'New Leads', 'subject_line' => 'Weekend Flash Sale!', 'message' => '48-hour flash sale, don\'t miss out.', 'send_date' => '2026-06-12', 'status' => 'scheduled'],
            ['name' => 'New Product SMS', 'type' => 'SMS', 'objective' => 'Awareness', 'channel' => 'SMS', 'audience' => 'Existing Customers', 'subject_line' => 'New Product Launch', 'message' => 'Check out our newest product line.', 'send_date' => '2026-05-24', 'status' => 'draft'],
            ['name' => 'Follow us on Instagram!', 'type' => 'Social', 'objective' => 'Engagement', 'channel' => 'Instagram', 'audience' => 'All Customers', 'subject_line' => 'Follow us!', 'message' => 'Stay updated with our latest posts.', 'send_date' => '2026-05-29', 'status' => 'scheduled'],
            ['name' => 'Loyalty Reward', 'type' => 'Email', 'objective' => 'Retention', 'channel' => 'Email', 'audience' => 'Loyalty Members', 'subject_line' => 'A reward just for you', 'message' => 'Enjoy this exclusive loyalty reward.', 'send_date' => '2026-06-01', 'status' => 'draft'],
        ];

        foreach ($campaigns as $c) {
            Campaign::create($c);
        }
    }
}