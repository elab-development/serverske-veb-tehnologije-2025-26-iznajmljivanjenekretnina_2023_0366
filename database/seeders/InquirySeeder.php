<?php

namespace Database\Seeders;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class InquirySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::where('role', User::ROLE_USER)->take(8)->get();
        $properties = Property::where('status', Property::STATUS_ACTIVE)->take(8)->get();

        $inquiries = [
            [
                'user_id' => $users[0]->id,
                'property_id' => $properties[0]->id,
                'message' => 'Zainteresovana sam za pregled stana ove nedelje.',
                'phone' => '+381641112233',
                'preferred_date' => now()->addDays(3)->toDateString(),
                'preferred_time' => '17:30:00',
                'status' => Inquiry::STATUS_NEW,
            ],
            [
                'user_id' => $users[1]->id,
                'property_id' => $properties[1]->id,
                'message' => 'Da li je moguce pogledati kucu tokom vikenda?',
                'phone' => '+381651234567',
                'preferred_date' => now()->addDays(5)->toDateString(),
                'preferred_time' => '11:00:00',
                'status' => Inquiry::STATUS_CONTACTED,
                'admin_note' => 'Klijent pozvan, ceka potvrdu termina.',
            ],
            [
                'user_id' => $users[2]->id,
                'property_id' => $properties[2]->id,
                'message' => 'Zanima me da li je stan pet friendly.',
                'phone' => '+381621112222',
                'preferred_date' => now()->addDays(2)->toDateString(),
                'preferred_time' => '18:00:00',
                'status' => Inquiry::STATUS_SCHEDULED,
                'admin_note' => 'Pregled zakazan.',
            ],
            [
                'user_id' => $users[3]->id,
                'property_id' => $properties[3]->id,
                'message' => 'Molim za vise informacija o prikljuccima na placu.',
                'phone' => '+381631231231',
                'preferred_date' => null,
                'preferred_time' => null,
                'status' => Inquiry::STATUS_NEW,
            ],
        ];

        foreach ($inquiries as $inquiry) {
            Inquiry::factory()->create($inquiry);
        }

        foreach ($users->skip(4)->values() as $index => $user) {
            Inquiry::factory()->create([
                'user_id' => $user->id,
                'property_id' => $properties[$index + 4]->id,
                'status' => Inquiry::STATUS_NEW,
            ]);
        }
    }
}
