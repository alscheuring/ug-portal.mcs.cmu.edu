<?php

namespace App\Filament\Resources\Teams\Schemas;

use App\Models\User;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $canManageTeamAdmins = $isSuperAdmin || $user->isTeamAdmin();

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

                Section::make('Team Administrators')
                    ->description('Manage users who have administrative access to this team')
                    ->schema([
                        Repeater::make('team_admins')
                            ->label('Team Administrators')
                            ->schema([
                                Select::make('user_id')
                                    ->label('User')
                                    ->required()
                                    ->options(function () {
                                        return User::whereHas('roles', function ($query) {
                                            $query->where('name', 'TeamAdmin');
                                        })
                                            ->orWhere(function ($query) {
                                                // Also include users without TeamAdmin role so they can be assigned
                                                $query->whereDoesntHave('roles', function ($subQuery) {
                                                    $subQuery->where('name', 'SuperAdmin');
                                                });
                                            })
                                            ->orderBy('name')
                                            ->get()
                                            ->mapWithKeys(function ($user) {
                                                $roleLabel = $user->hasRole('TeamAdmin') ? ' (Team Admin)' : '';

                                                return [$user->id => "{$user->name} ({$user->email}){$roleLabel}"];
                                            })
                                            ->toArray();
                                    })
                                    ->searchable()
                                    ->live(),
                            ])
                            ->defaultItems(0)
                            ->addActionLabel('Add Team Administrator')
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(function (array $state): string {
                                if (! isset($state['user_id'])) {
                                    return 'Select a user';
                                }

                                $user = User::find($state['user_id']);

                                return $user ? "{$user->name} ({$user->email})" : 'Unknown user';
                            })
                            ->helperText('Add users who should have Team Administrator privileges for this team. Users will be assigned the TeamAdmin role and set to this team.')
                            ->visible($canManageTeamAdmins),
                    ])
                    ->visible($canManageTeamAdmins)
                    ->collapsible()
                    ->collapsed(false),

            ]);
    }
}
