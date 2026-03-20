<?php

namespace App\Filament\Resources\Polls\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PollForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Poll Details')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->maxLength(1000)
                            ->helperText('Optional description to provide context for the poll')
                            ->columnSpanFull(),

                        Select::make('team_id')
                            ->relationship('team', 'name')
                            ->required()
                            ->visible(fn () => auth()->user()->isSuperAdmin())
                            ->helperText('SuperAdmins can choose any team, TeamAdmins are automatically assigned to their team'),

                        Hidden::make('team_id')
                            ->default(fn () => auth()->user()->isSuperAdmin() ? null : auth()->user()->current_team_id)
                            ->visible(fn () => ! auth()->user()->isSuperAdmin()),

                        Hidden::make('created_by')
                            ->default(fn () => auth()->id()),
                    ])
                    ->columns(2),

                Section::make('Poll Options')
                    ->schema([
                        Repeater::make('options')
                            ->relationship()
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->placeholder('Option title'),

                                Textarea::make('description')
                                    ->maxLength(500)
                                    ->placeholder('Optional description'),

                                Hidden::make('sort_order')
                                    ->default(fn ($component) => $component->getContainer()->getParentComponent()->getChildComponentsCount()),
                            ])
                            ->defaultItems(2)
                            ->minItems(2)
                            ->maxItems(10)
                            ->reorderable()
                            ->collapsible()
                            ->cloneable()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Option')
                            ->columnSpanFull(),
                    ]),

                Section::make('Poll Settings')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Toggle to activate/deactivate this poll'),

                        Toggle::make('allows_multiple_votes')
                            ->label('Allow Multiple Votes')
                            ->helperText('Allow users to vote for multiple options'),

                        Toggle::make('show_results_before_voting')
                            ->label('Show Results Before Voting')
                            ->helperText('Show current results to users before they vote'),

                        DateTimePicker::make('starts_at')
                            ->label('Start Date')
                            ->helperText('Leave blank to start immediately')
                            ->native(false),

                        DateTimePicker::make('ends_at')
                            ->label('End Date')
                            ->helperText('Leave blank for no end date')
                            ->native(false),
                    ])
                    ->columns(2),
            ]);
    }
}
