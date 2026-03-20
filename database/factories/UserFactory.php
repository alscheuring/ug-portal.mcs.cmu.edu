<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departments = [
            'Biological Sciences',
            'Mathematical Sciences',
            'Chemistry',
            'Physics',
        ];

        $years = ['Freshman', 'Sophomore', 'Junior', 'Senior'];
        $majors = [
            'Biology',
            'Mathematics',
            'Chemistry',
            'Physics',
            'Computer Science',
            'Statistics',
        ];

        $andrewId = fake()->unique()->userName();

        return [
            'name' => fake()->name(),
            'email' => $andrewId.'@andrew.cmu.edu',
            'andrew_id' => $andrewId,
            'department' => fake()->randomElement($departments),
            'year_in_program' => fake()->randomElement($years),
            'major' => fake()->randomElement($majors),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'profile_completed_at' => fake()->optional(0.7)->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create a user with incomplete profile.
     */
    public function incompleteProfile(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_completed_at' => null,
            'department' => null,
            'year_in_program' => null,
            'major' => null,
        ]);
    }

    /**
     * Create a user with completed profile.
     */
    public function completedProfile(): static
    {
        return $this->state(fn (array $attributes) => [
            'profile_completed_at' => now(),
        ]);
    }

    /**
     * Create a SuperAdmin user.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Albert Scheuring',
            'email' => 'alberts@andrew.cmu.edu',
            'andrew_id' => 'alberts',
            'department' => 'Mellon College of Science',
            'profile_completed_at' => now(),
        ]);
    }
}
