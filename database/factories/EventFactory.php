<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventFeed;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('now', '+6 months');
        $endDate = $this->faker->dateTimeBetween($startDate, $startDate->format('Y-m-d H:i:s').' +4 hours');

        $title = $this->faker->words(3, true);

        return [
            'title' => ucwords($title),
            'slug' => Event::generateSlug($title),
            'description' => $this->faker->paragraphs(3, true),
            'summary' => $this->faker->sentence(),
            'start_datetime' => $startDate,
            'end_datetime' => $endDate,
            'location' => $this->faker->optional(0.8)->randomElement([
                'Gates Hillman Center',
                'Newell-Simon Hall',
                'Wean Hall',
                'Hunt Library',
                'University Center',
                'Cohon Center',
                'Mellon Institute',
                'Baker Hall',
                'Online Event',
                'TBD',
            ]),
            'info_url' => $this->faker->optional(0.6)->url(),
            'image_url' => $this->faker->optional(0.4)->imageUrl(800, 600, 'events'),
            'tags' => $this->faker->optional(0.7)->randomElements([
                'academic',
                'research',
                'seminar',
                'workshop',
                'conference',
                'social',
                'networking',
                'graduation',
                'orientation',
                'meeting',
                'lecture',
                'presentation',
                'competition',
                'career',
                'volunteer',
            ], $this->faker->numberBetween(1, 4)),
            'is_published' => $this->faker->boolean(85),
            'source_type' => 'manual',
            'external_id' => null,
            'team_id' => Team::factory(),
            'author_id' => User::factory(),
            'event_feed_id' => null,
        ];
    }

    /**
     * Indicate that the event is imported from an external feed.
     */
    public function imported(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'imported',
            'external_id' => $this->faker->uuid(),
            'event_feed_id' => EventFeed::factory(),
            'author_id' => null, // Imported events don't have authors
        ]);
    }

    /**
     * Indicate that the event is manually created.
     */
    public function manual(): static
    {
        return $this->state(fn (array $attributes) => [
            'source_type' => 'manual',
            'external_id' => null,
            'event_feed_id' => null,
        ]);
    }

    /**
     * Indicate that the event is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => true,
        ]);
    }

    /**
     * Indicate that the event is unpublished.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }

    /**
     * Indicate that the event is upcoming.
     */
    public function upcoming(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = $this->faker->dateTimeBetween('now', '+6 months');
            $endDate = $this->faker->dateTimeBetween($startDate, $startDate->format('Y-m-d H:i:s').' +4 hours');

            return [
                'start_datetime' => $startDate,
                'end_datetime' => $endDate,
            ];
        });
    }

    /**
     * Indicate that the event is in the past.
     */
    public function past(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = $this->faker->dateTimeBetween('-6 months', '-1 day');
            $endDate = $this->faker->dateTimeBetween($startDate, $startDate->format('Y-m-d H:i:s').' +4 hours');

            return [
                'start_datetime' => $startDate,
                'end_datetime' => $endDate,
            ];
        });
    }

    /**
     * Indicate that the event is currently happening.
     */
    public function happening(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = $this->faker->dateTimeBetween('-2 hours', '-1 hour');
            $endDate = $this->faker->dateTimeBetween('+1 hour', '+4 hours');

            return [
                'start_datetime' => $startDate,
                'end_datetime' => $endDate,
            ];
        });
    }

    /**
     * Indicate that the event is a full-day event.
     */
    public function fullDay(): static
    {
        return $this->state(function (array $attributes) {
            $date = $this->faker->dateTimeBetween('now', '+6 months');
            $startDate = $date->setTime(0, 0, 0);
            $endDate = clone $startDate;
            $endDate->setTime(23, 59, 59);

            return [
                'start_datetime' => $startDate,
                'end_datetime' => $endDate,
            ];
        });
    }

    /**
     * Indicate that the event is a multi-day event.
     */
    public function multiDay(): static
    {
        return $this->state(function (array $attributes) {
            $startDate = $this->faker->dateTimeBetween('now', '+6 months');
            $endDate = $this->faker->dateTimeBetween($startDate->format('Y-m-d').' +1 day', $startDate->format('Y-m-d').' +5 days');

            return [
                'start_datetime' => $startDate,
                'end_datetime' => $endDate,
            ];
        });
    }

    /**
     * Indicate that the event has specific tags.
     */
    public function withTags(array $tags): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => $tags,
        ]);
    }

    /**
     * Indicate that the event is for a specific team.
     */
    public function forTeam(Team $team): static
    {
        return $this->state(fn (array $attributes) => [
            'team_id' => $team->id,
        ]);
    }

    /**
     * Indicate that the event is authored by a specific user.
     */
    public function authoredBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'author_id' => $user->id,
        ]);
    }

    /**
     * Create an academic seminar event.
     */
    public function seminar(): static
    {
        return $this->state(function (array $attributes) {
            $speakers = [
                'Dr. Jane Smith',
                'Prof. John Doe',
                'Dr. Sarah Wilson',
                'Prof. Michael Brown',
                'Dr. Lisa Chen',
            ];

            $speaker = $this->faker->randomElement($speakers);
            $topic = $this->faker->randomElement([
                'Machine Learning Applications in Healthcare',
                'Quantum Computing and Future Technologies',
                'Cybersecurity in the Modern Age',
                'Sustainable Computing Practices',
                'Human-Computer Interaction Research',
                'Artificial Intelligence Ethics',
                'Blockchain Technology and Society',
                'Data Science for Social Good',
            ]);

            return [
                'title' => "{$speaker}: {$topic}",
                'slug' => Event::generateSlug("{$speaker}: {$topic}"),
                'summary' => "A research seminar featuring {$speaker} discussing {$topic}",
                'description' => "Join us for an insightful seminar where {$speaker} will present their latest research on {$topic}. This talk will cover recent developments, practical applications, and future directions in the field.",
                'tags' => ['seminar', 'research', 'academic'],
                'location' => $this->faker->randomElement([
                    'Gates Hillman Center - Room 6115',
                    'Newell-Simon Hall - Room 1507',
                    'Wean Hall - Room 5409',
                ]),
            ];
        });
    }

    /**
     * Create a social event.
     */
    public function social(): static
    {
        return $this->state(function (array $attributes) {
            $events = [
                'Welcome Back Mixer',
                'End of Semester Celebration',
                'Halloween Party',
                'Holiday Gathering',
                'Game Night',
                'Pizza and Networking',
                'Student Social Hour',
                'Department Picnic',
            ];

            $title = $this->faker->randomElement($events);

            return [
                'title' => $title,
                'slug' => Event::generateSlug($title),
                'summary' => 'Join us for a fun social event with fellow students and faculty',
                'description' => 'Come and connect with your peers in a relaxed, friendly environment. Food and refreshments will be provided.',
                'tags' => ['social', 'networking', 'students'],
                'location' => $this->faker->randomElement([
                    'University Center - Great Hall',
                    'Cohon Center - Rangos Ballroom',
                    'Gates Hillman Center - Commons',
                ]),
            ];
        });
    }
}
