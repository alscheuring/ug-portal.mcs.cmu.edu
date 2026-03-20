<?php

namespace App\Filament\Resources\MenuItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MenuItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('menu.name')
                    ->label('Menu')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('menu.team.name')
                    ->label('Team')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('blue')
                    ->visible(fn () => auth()->user()->isSuperAdmin()),
                TextColumn::make('parent.title')
                    ->searchable(),
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('link_type')
                    ->searchable(),
                TextColumn::make('page.title')
                    ->searchable(),
                TextColumn::make('external_url')
                    ->searchable(),
                IconColumn::make('opens_in_new_tab')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->boolean(),
                TextColumn::make('css_class')
                    ->searchable(),
                TextColumn::make('icon')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('menu.team_id')
                    ->label('Team')
                    ->relationship('menu.team', 'name')
                    ->visible(fn () => auth()->user()->isSuperAdmin()),

                SelectFilter::make('menu_id')
                    ->label('Menu')
                    ->relationship('menu', 'name')
                    ->placeholder('All Menus'),

                SelectFilter::make('link_type')
                    ->label('Link Type')
                    ->options([
                        'page' => 'Page',
                        'external' => 'External URL',
                        'divider' => 'Divider',
                    ])
                    ->placeholder('All Types'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
