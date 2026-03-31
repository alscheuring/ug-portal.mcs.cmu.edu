<?php

namespace App\Filament\Resources\LayupPages\Pages;

use App\Filament\Resources\LayupPages\LayupPageResource;
use App\Models\Sidebar;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditLayupPage extends EditRecord
{
    protected static string $resource = LayupPageResource::class;

    protected $sidebarAssignments = [];

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

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load the sidebar assignments for the repeater
        $sidebarAssignments = $this->record->sidebars()
            ->orderBy('layup_page_sidebar.sort_order')
            ->get()
            ->map(function ($sidebar) {
                return [
                    'sidebar_id' => $sidebar->id,
                ];
            })
            ->toArray();

        $data['sidebar_assignments'] = $sidebarAssignments;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Set published_at when status changes to published
        if ($data['status'] === 'published' &&
            ($this->record->status !== 'published' || $this->record->published_at === null)) {
            $data['published_at'] = now();
        }

        // Clear published_at when status changes from published to draft
        if ($data['status'] !== 'published' && $this->record->status === 'published') {
            $data['published_at'] = null;
        }

        // Handle sidebar assignments separately
        $sidebarAssignments = $data['sidebar_assignments'] ?? [];
        unset($data['sidebar_assignments']);

        // We'll handle the sidebar sync after the main record is saved
        $this->sidebarAssignments = $sidebarAssignments;

        return $data;
    }

    protected function afterSave(): void
    {
        // Sync the sidebars with proper sort order
        if (isset($this->sidebarAssignments)) {
            $syncData = [];
            foreach ($this->sidebarAssignments as $index => $assignment) {
                if (isset($assignment['sidebar_id'])) {
                    $syncData[$assignment['sidebar_id']] = [
                        'sort_order' => $index,
                    ];
                }
            }

            $this->record->sidebars()->sync($syncData);
        }
    }
}
