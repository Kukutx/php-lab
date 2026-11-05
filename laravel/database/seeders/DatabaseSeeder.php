<?php

namespace Database\Seeders;

use App\Enums\NoticeCategory;
use App\Models\Notice;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $notices = [
            ['Road closure in Via Roma for pipe works', NoticeCategory::Roadworks, 'Via Roma will be closed to traffic between number 10 and 40 from Monday to Friday, 8:00-18:00, for water pipe replacement. Buses 3 and 7 are diverted via Corso Italia.'],
            ['Summer festival in the main square', NoticeCategory::Events, 'Music, local food and activities for children every evening from 18 to 21 July in Piazza Garibaldi. Free entry; the square is closed to cars from 16:00.'],
            ['New opening hours of the registry office', NoticeCategory::Services, 'From 1 December the registry office is open Monday to Friday 8:30-12:30 and Tuesday and Thursday afternoons 14:30-17:00. Appointments can be booked online.'],
            ['Weather alert: heavy rain expected', NoticeCategory::Emergency, 'Civil protection has issued an orange alert for tomorrow. Avoid underpasses and riverbanks, secure objects on balconies and follow the official channels for updates.'],
            ['Waste collection calendar for the holidays', NoticeCategory::Services, 'During the holidays door-to-door collection follows a different calendar. Paper on 24 December is moved to 23 December; glass on 1 January is moved to 2 January.'],
        ];

        foreach ($notices as $i => [$title, $category, $body]) {
            Notice::create([
                'title' => $title,
                'category' => $category,
                'body' => $body,
                'published_at' => now()->subDays($i * 2),
            ]);
        }

        Notice::factory()->count(4)->create();
        Notice::factory()->draft()->create(['title' => 'Draft: council meeting agenda']);
    }
}
