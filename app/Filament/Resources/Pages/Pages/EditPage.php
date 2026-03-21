<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(function () {
                    $page = $this->getRecord();
                    if ($page && $page->team && $page->is_published) {
                        return route('public.team.page', [$page->team->slug, $page->slug]);
                    }
                    return null;
                })
                ->visible(function () {
                    $page = $this->getRecord();
                    return $page && $page->team && $page->is_published;
                })
                ->openUrlInNewTab(false),

            Action::make('visit')
                ->label('Visit')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('primary')
                ->url(function () {
                    $page = $this->getRecord();
                    if ($page && $page->team && $page->is_published) {
                        return route('public.team.page', [$page->team->slug, $page->slug]);
                    }
                    return null;
                })
                ->visible(function () {
                    $page = $this->getRecord();
                    return $page && $page->team && $page->is_published;
                })
                ->openUrlInNewTab(true),

            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
