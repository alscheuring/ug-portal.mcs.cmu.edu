<?php

namespace App\Filament\Resources\LayupPages\Pages;

use App\Filament\Resources\LayupPages\LayupPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLayupPage extends CreateRecord
{
    protected static string $resource = LayupPageResource::class;

    protected $sidebarAssignments = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set author to current user
        $data['author_id'] = auth()->id();

        // Set team_id if not already set (for TeamAdmins)
        if (! isset($data['team_id']) || empty($data['team_id'])) {
            $data['team_id'] = auth()->user()->current_team_id;
        }

        // Set published_at if status is published
        if ($data['status'] === 'published' && ! isset($data['published_at'])) {
            $data['published_at'] = now();
        }

        // Handle sidebar assignments separately
        $sidebarAssignments = $data['sidebar_assignments'] ?? [];
        unset($data['sidebar_assignments']);

        // Store for after creation
        $this->sidebarAssignments = $sidebarAssignments;

        return $data;
    }

    protected function afterCreate(): void
    {
        // Sync the sidebars with proper sort order
        if (! empty($this->sidebarAssignments)) {
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

        parent::afterCreate();
    }
}
