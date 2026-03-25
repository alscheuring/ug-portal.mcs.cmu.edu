<?php

namespace Database\Factories;

use App\Models\EventFeed;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventFeed>
 */
class EventFeedFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true).' Events Feed',
            'api_url' => $this->faker->url(),
            'max_events' => $this->faker->numberBetween(10, 100),
            'is_active' => $this->faker->boolean(80),
            'import_settings' => EventFeed::getGenericJsonFeedSettings(),
            'last_imported_at' => $this->faker->optional(0.7)->dateTimeBetween('-30 days', 'now'),
            'team_id' => Team::factory(),
        ];
    }

    /**
     * Indicate that the feed is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the feed is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the feed has been recently imported.
     */
    public function recentlyImported(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_imported_at' => now()->subHours($this->faker->numberBetween(1, 12)),
        ]);
    }

    /**
     * Indicate that the feed has never been imported.
     */
    public function neverImported(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_imported_at' => null,
        ]);
    }

    /**
     * Indicate that the feed needs to be imported (imported more than 24 hours ago).
     */
    public function needsImport(): static
    {
        return $this->state(fn (array $attributes) => [
            'last_imported_at' => now()->subDays($this->faker->numberBetween(2, 10)),
        ]);
    }

    /**
     * Create a CMU Events API feed.
     */
    public function cmuEvents(): static
    {
        return $this->state(function (array $attributes) {
            $departments = [
                'Computer Science',
                'Robotics Institute',
                'Machine Learning Department',
                'Language Technologies Institute',
                'Human-Computer Interaction Institute',
                'Software and Societal Systems',
                'Electrical and Computer Engineering',
                'Biological Sciences',
                'Chemistry',
                'Physics',
                'Mathematics',
                'Statistics',
            ];

            $department = $this->faker->randomElement($departments);
            $encodedDepartment = urlencode($department);

            return [
                'name' => "{$department} Events",
                'api_url' => "https://events.cmu.edu/live/json/v2/events/group/{$encodedDepartment}/max/50/response_fields/tags,group_title,image,summary,description,location",
                'max_events' => 50,
                'import_settings' => EventFeed::getCmuEventsFeedSettings(),
            ];
        });
    }

    /**
     * Create a generic JSON feed.
     */
    public function genericJson(): static
    {
        return $this->state(fn (array $attributes) => [
            'import_settings' => EventFeed::getGenericJsonFeedSettings(),
        ]);
    }

    /**
     * Indicate that the feed is for a specific team.
     */
    public function forTeam(Team $team): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $team->id,
        ]);
    }

    /**
     * Create a feed with custom import settings.
     */
    public function withImportSettings(array $settings): static
    {
        return $this->state(fn (array $attributes) => [
            'import_settings' => $settings,
        ]);
    }

    /**
     * Create a feed with a specific max events limit.
     */
    public function withMaxEvents(int $maxEvents): static
    {
        return $this->state(fn (array $attributes) => [
            'max_events' => $maxEvents,
        ]);
    }

    /**
     * Create a sample university calendar feed.
     */
    public function universityCalendar(): static
    {
        return $this->state(function (array $attributes) {
            $calendarTypes = [
                'Academic Calendar',
                'Events Calendar',
                'Lectures & Seminars',
                'Student Activities',
                'Research Events',
                'Campus News',
                'Department Events',
            ];

            $type = $this->faker->randomElement($calendarTypes);

            return [
                'name' => $type,
                'api_url' => 'https://calendar.university.edu/api/events.json',
                'max_events' => 25,
                'import_settings' => [
                    'type' => 'university_calendar',
                    'field_mapping' => [
                        'title' => 'event_title',
                        'description' => 'event_description',
                        'summary' => 'event_summary',
                        'start_datetime' => 'start_time',
                        'end_datetime' => 'end_time',
                        'location' => 'venue',
                        'info_url' => 'event_url',
                        'image_url' => 'featured_image',
                        'tags' => 'categories',
                        'external_id' => 'event_id',
                    ],
                    'date_format' => 'Y-m-d\TH:i:s\Z',
                    'timezone' => 'America/New_York',
                ],
            ];
        });
    }
}
