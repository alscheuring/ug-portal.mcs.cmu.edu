<?php

namespace Database\Seeders;

use App\Models\Team;
use Illuminate\Database\Seeder;

class PopulateTeamSidebarContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Add example content to Biological Sciences team to demonstrate the feature
        $biosciTeam = Team::where('slug', 'biosci')->first();
        if ($biosciTeam) {
            $biosciTeam->update([
                'quick_links_title' => 'Student Resources',
                'quick_links_content' => '<ul>
<li><a href="/biosci">Department Home</a></li>
<li><a href="/biosci/announcements">Latest News</a></li>
<li><a href="https://www.cmu.edu/bio/" target="_blank">Department Website</a></li>
<li><a href="https://www.cmu.edu/bio/undergraduate/" target="_blank">Undergraduate Programs</a></li>
<li><a href="/biosci/polls">Current Polls</a></li>
</ul>',
                'help_box_title' => 'Questions?',
                'help_box_content' => '<p><strong>Need advising help?</strong></p>
<p>Contact your academic advisor or visit our <a href="/biosci/meet-your-advisors">Meet Your Advisors</a> page.</p>
<p><strong>Technical issues?</strong></p>
<p>Email <a href="mailto:kkovacs@andrew.cmu.edu">kkovacs@andrew.cmu.edu</a></p>
<p><strong>General questions?</strong></p>
<p>Visit the <a href="https://www.cmu.edu/bio/" target="_blank">Department Website</a> or stop by our office.</p>',
            ]);
            $this->command->info('Added example sidebar content to Biological Sciences team.');
        }

        $this->command->info('Team sidebar content seeding completed.');
    }
}
