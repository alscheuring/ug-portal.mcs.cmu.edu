<?php

namespace App\Filament\Resources\Teams\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();

        return $schema
            ->columns(1)
            ->components([
                Section::make('Team Information')
                    ->schema(array_filter([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->disabled(! $isSuperAdmin),

                        $isSuperAdmin ? TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('URL-friendly version of the team name') : null,

                        TextInput::make('manager_email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->disabled(! $isSuperAdmin),

                        Textarea::make('description')
                            ->rows(3),

                        $isSuperAdmin ? Toggle::make('is_active')
                            ->default(true) : null,
                    ])),

            ]);
    }
}
