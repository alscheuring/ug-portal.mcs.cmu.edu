<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Menu Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if (! $get('slug')) {
                                    $set('slug', Str::slug($state));
                                }
                            })
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->alphaDash()
                            ->unique(ignoreRecord: true)
                            ->helperText('URL-friendly version of the menu name')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->maxLength(500)
                            ->helperText('Optional description for this menu')
                            ->columnSpanFull(),
                    ]),

                Section::make('Settings')
                    ->schema([
                        Select::make('team_id')
                            ->relationship('team', 'name')
                            ->required()
                            ->visible(fn () => auth()->user()->isSuperAdmin())
                            ->helperText('SuperAdmins can assign menus to any team'),

                        Hidden::make('team_id')
                            ->default(fn () => auth()->user()->current_team_id)
                            ->visible(fn () => ! auth()->user()->isSuperAdmin()),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active menus will be displayed on the public portal'),
                    ])
                    ->columns(2),
            ]);
    }
}
