<?php

namespace App\Filament\Resources\LayupPages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class LayupPagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_department_home')
                    ->label('Dept. Home')
                    ->boolean()
                    ->tooltip('Department home pages cannot be deleted')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn ($record) => ! $record->is_department_home),
                RestoreAction::make()
                    ->visible(fn ($record) => ! $record->is_department_home),
                ForceDeleteAction::make()
                    ->visible(fn ($record) => ! $record->is_department_home),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records) {
                            // Filter out department home pages
                            $deletableRecords = $records->reject(fn ($record) => $record->is_department_home);

                            if ($deletableRecords->isEmpty()) {
                                Notification::make()
                                    ->title('Cannot delete department home pages')
                                    ->body('The selected pages are department home pages and cannot be deleted.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            // Delete only the deletable records
                            $deletableRecords->each->delete();

                            $skipped = $records->count() - $deletableRecords->count();
                            if ($skipped > 0) {
                                Notification::make()
                                    ->title('Partial deletion completed')
                                    ->body("{$skipped} department home pages were skipped and cannot be deleted.")
                                    ->warning()
                                    ->send();
                            }
                        }),
                    ForceDeleteBulkAction::make()
                        ->deselectRecordsAfterCompletion()
                        ->action(function ($records) {
                            // Filter out department home pages
                            $deletableRecords = $records->reject(fn ($record) => $record->is_department_home);

                            if ($deletableRecords->isEmpty()) {
                                Notification::make()
                                    ->title('Cannot delete department home pages')
                                    ->body('The selected pages are department home pages and cannot be deleted.')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            // Force delete only the deletable records
                            $deletableRecords->each->forceDelete();

                            $skipped = $records->count() - $deletableRecords->count();
                            if ($skipped > 0) {
                                Notification::make()
                                    ->title('Partial force deletion completed')
                                    ->body("{$skipped} department home pages were skipped and cannot be deleted.")
                                    ->warning()
                                    ->send();
                            }
                        }),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
