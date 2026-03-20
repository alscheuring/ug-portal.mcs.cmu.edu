<?php

namespace App\Filament\Resources\Menus\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('team.name')
                    ->label('Team')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('blue')
                    ->visible(fn () => auth()->user()->isSuperAdmin()),

                TextColumn::make('items_count')
                    ->label('Menu Items')
                    ->counts('items')
                    ->sortable()
                    ->badge()
                    ->color('green'),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon(Heroicon::OutlinedCheckBadge)
                    ->falseIcon(Heroicon::OutlinedXMark)
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        true => 'Active',
                        false => 'Inactive',
                    ])
                    ->placeholder('All Menus'),

                SelectFilter::make('team_id')
                    ->label('Team')
                    ->relationship('team', 'name')
                    ->visible(fn () => auth()->user()->isSuperAdmin()),
            ])
            ->recordActions([
                Action::make('manage_items')
                    ->label('Manage Items')
                    ->icon(Heroicon::OutlinedListBullet)
                    ->color('gray')
                    ->url(fn ($record) => "/admin/menu-items?tableFilters[menu_id][value]={$record->id}"),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->emptyStateHeading('No menus found')
            ->emptyStateDescription('Create your first menu to get started with navigation management.');
    }
}
