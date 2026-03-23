<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\Team;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                    ])
                    ->columns(2),

                Section::make('CMU Information')
                    ->schema([
                        Select::make('department')
                            ->options([
                                'Biological Sciences' => 'Biological Sciences',
                                'Mathematical Sciences' => 'Mathematical Sciences',
                                'Chemistry' => 'Chemistry',
                                'Physics' => 'Physics',
                                'Mellon College of Science' => 'Mellon College of Science',
                            ]),

                        Select::make('year_in_program')
                            ->options([
                                'Freshman' => 'Freshman',
                                'Sophomore' => 'Sophomore',
                                'Junior' => 'Junior',
                                'Senior' => 'Senior',
                                'Graduate' => 'Graduate',
                            ]),

                        TextInput::make('major')
                            ->maxLength(255),

                        DateTimePicker::make('profile_completed_at')
                            ->label('Profile Completed At'),
                    ])
                    ->columns(2),

                Section::make('Team & Role Assignment')
                    ->schema([
                        Select::make('current_team_id')
                            ->label('Team')
                            ->relationship('team', 'name')
                            ->options(Team::pluck('name', 'id')),

                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->options(Role::pluck('name', 'id')),
                    ])
                    ->columns(2),
            ]);
    }
}
