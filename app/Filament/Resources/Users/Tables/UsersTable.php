<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use STS\FilamentImpersonate\Actions\Impersonate;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                  TextColumn::make('department')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('team.name')
                    ->label('Team')
                    ->sortable(),

                BadgeColumn::make('roles.name')
                    ->label('Roles')
                    ->separator(','),

                IconColumn::make('profile_completed_at')
                    ->label('Profile Complete')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->getStateUsing(fn ($record) => ! is_null($record->profile_completed_at)),

                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('department')
                    ->options([
                        'Biological Sciences' => 'Biological Sciences',
                        'Mathematical Sciences' => 'Mathematical Sciences',
                        'Chemistry' => 'Chemistry',
                        'Physics' => 'Physics',
                        'Mellon College of Science' => 'Mellon College of Science',
                    ]),

                SelectFilter::make('roles')
                    ->relationship('roles', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
                Impersonate::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
