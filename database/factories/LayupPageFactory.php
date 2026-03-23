<?php

namespace Database\Factories;

use App\Models\LayupPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LayupPage>
 */
class LayupPageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'slug' => $this->faker->slug(),
            'content' => [
                'rows' => [
                    [
                        'id' => 'row_'.str_replace('-', '', \Str::random(8)),
                        'order' => 0,
                        'columns' => [
                            [
                                'id' => 'col_'.str_replace('-', '', \Str::random(8)),
                                'span' => [
                                    'xl' => 12,
                                    'lg' => 12,
                                    'md' => 12,
                                    'sm' => 12,
                                ],
                                'widgets' => [
                                    [
                                        'id' => 'widget_'.str_replace('-', '', \Str::random(8)),
                                        'type' => 'text',
                                        'data' => [
                                            'content' => '<p>'.$this->faker->paragraphs(2, true).'</p>',
                                            'text_align' => null,
                                            'text_color' => null,
                                            'background_color' => null,
                                            'padding' => [
                                                'top' => null,
                                                'right' => null,
                                                'bottom' => null,
                                                'left' => null,
                                                'unit' => null,
                                            ],
                                            'margin' => [
                                                'top' => null,
                                                'right' => null,
                                                'bottom' => null,
                                                'left' => null,
                                                'unit' => null,
                                            ],
                                            'hide_on' => [],
                                            'animation' => null,
                                            'class' => null,
                                            'id' => null,
                                        ],
                                    ],
                                ],
                                'settings' => [
                                    'padding' => 'p-4',
                                    'background' => 'transparent',
                                ],
                            ],
                        ],
                        'settings' => [
                            'wrap' => 'wrap',
                            'align' => 'stretch',
                            'justify' => 'start',
                            'direction' => 'row',
                            'full_width' => false,
                        ],
                    ],
                ],
            ],
            'status' => 'published',
            'meta' => [],
            'team_id' => 1, // Will be overridden in tests
            'author_id' => 1, // Will be overridden in tests
            'published_at' => now(),
        ];
    }
}
