<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Awcodes\Curator\Components\Forms\CuratorPicker;

class TeamMemberBlock extends PageBlock
{
    protected static string $name = 'team-members';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                TextInput::make('heading')
                    ->label('Section Heading')
                    ->default('About the team')
                    ->required(),

                Textarea::make('description')
                    ->label('Team Description')
                    ->default("We're a dynamic group of individuals who are passionate about what we do and dedicated to delivering the best results for our clients.")
                    ->rows(3),

                Repeater::make('members')
                    ->label('Team Members')
                    ->schema([
                        CuratorPicker::make('image')
                            ->label('Profile Image')
                            ->required()
                            ->buttonLabel('Select Photo')
                            ->helperText('Choose a professional headshot'),

                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->placeholder('e.g. Leslie Alexander'),

                        TextInput::make('title')
                            ->label('Job Title')
                            ->required()
                            ->placeholder('e.g. Co-Founder / CEO'),

                        Textarea::make('bio')
                            ->label('Biography')
                            ->required()
                            ->rows(3)
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
                    ->maxItems(12)
                    ->addActionLabel('Add Team Member')
                    ->defaultItems(1),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}