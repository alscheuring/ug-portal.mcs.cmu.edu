<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Awcodes\Curator\Components\Forms\CuratorPicker;

class LeadershipGridBlock extends PageBlock
{
    protected static string $name = 'leadership-grid';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                TextInput::make('heading')
                    ->label('Section Heading')
                    ->default('Meet our leadership')
                    ->required(),

                Textarea::make('description')
                    ->label('Leadership Description')
                    ->default("We're a dynamic group of individuals who are passionate about what we do and dedicated to delivering the best results for our clients.")
                    ->rows(3),

                Repeater::make('leaders')
                    ->label('Leadership Team')
                    ->schema([
                        CuratorPicker::make('image')
                            ->label('Profile Image')
                            ->required()
                            ->buttonLabel('Select Photo')
                            ->helperText('Choose a professional headshot'),

                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->placeholder('e.g. Leonard Krasner'),

                        TextInput::make('title')
                            ->label('Job Title')
                            ->required()
                            ->placeholder('e.g. Senior Designer'),

                        Textarea::make('bio')
                            ->label('Biography')
                            ->required()
                            ->rows(4)
                            ->placeholder('Brief description of their role and background'),

                        TextInput::make('twitter_url')
                            ->label('Twitter/X URL')
                            ->url()
                            ->placeholder('https://twitter.com/username'),

                        TextInput::make('linkedin_url')
                            ->label('LinkedIn URL')
                            ->url()
                            ->placeholder('https://linkedin.com/in/username'),
                    ])
                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                    ->collapsed()
                    ->cloneable()
                    ->reorderable()
                    ->minItems(1)
                    ->maxItems(8)
                    ->addActionLabel('Add Leader')
                    ->defaultItems(4)
                    ->columns(2),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}