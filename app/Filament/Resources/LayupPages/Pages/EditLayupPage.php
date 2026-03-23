<?php

namespace App\Filament\Resources\LayupPages\Pages;

use App\Filament\Resources\LayupPages\LayupPageResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditLayupPage extends EditRecord
{
    protected static string $resource = LayupPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn () => $this->record->getUrl())
                ->openUrlInNewTab()
                ->visible(fn () => $this->record->team && $this->record->slug)
                ->badge(function () {
                    if (! $this->record->isPublished()) {
                        return 'Draft';
                    }

                    if ($this->record->isDepartmentHome()) {
                        return 'Dept. Home';
                    }

                    return null;
                })
                ->badgeColor(function () {
                    if (! $this->record->isPublished()) {
                        return 'warning';
                    }

                    if ($this->record->isDepartmentHome()) {
                        return 'success';
                    }

                    return null;
                })
                ->tooltip(function () {
                    if (! $this->record->isPublished()) {
                        return 'This page is a draft and may not be publicly visible';
                    }

                    if ($this->record->isDepartmentHome()) {
                        return 'This is the main department page - changes will be visible immediately';
                    }

                    return 'Preview how this page appears to visitors';
                }),

            DeleteAction::make()
                ->visible(fn () => ! $this->record->isDepartmentHome()),
            ForceDeleteAction::make()
                ->visible(fn () => ! $this->record->isDepartmentHome()),
            RestoreAction::make()
                ->visible(fn () => ! $this->record->isDepartmentHome()),
        ];
    }
}
