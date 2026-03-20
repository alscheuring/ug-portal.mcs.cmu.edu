<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class CleanupTeamContactContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultContent = '<div>
<p><strong>Location:</strong><br>
Carnegie Mellon University<br>
Mellon College of Science<br>
Pittsburgh, PA 15213</p>
</div>';

        Team::query()->update(['contact_content' => $defaultContent]);

        $this->command->info('Updated all teams with clean default contact content.');
    }
}
