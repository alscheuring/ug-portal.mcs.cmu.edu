<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class UpdateTeamContactInfoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultContactInfo = [
            'contact_title' => 'Get in Touch',
            'address_line_1' => 'Carnegie Mellon University',
            'address_line_2' => 'Mellon College of Science',
            'address_line_3' => 'Pittsburgh, PA 15213',
        ];

        // Update existing teams with default contact information
        Team::where('contact_title', null)->update($defaultContactInfo);

        $this->command->info('Updated teams with default contact information.');
    }
}
