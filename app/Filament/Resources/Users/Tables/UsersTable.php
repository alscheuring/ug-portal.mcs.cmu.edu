<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

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

                Action::make('impersonate')
                    ->label('Impersonate')
                    ->icon('heroicon-o-user-circle')
                    ->color('warning')
                    ->visible(function ($record) {
                        return auth()->user()->canImpersonate($record) && $record->canBeImpersonated();
                    })
                    ->url(fn ($record) => route('impersonate.take', $record->id))
                    ->openUrlInNewTab(false)
                    ->requiresConfirmation()
                    ->modalHeading('Impersonate User')
                    ->modalDescription(fn ($record) => "Are you sure you want to impersonate {$record->name}?"),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
