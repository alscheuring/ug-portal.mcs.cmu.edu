<?php

namespace App\Filament\Resources\LayupPages\Pages;

use App\Filament\Resources\LayupPages\LayupPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLayupPage extends CreateRecord
{
    protected static string $resource = LayupPageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set author to current user
        $data['author_id'] = auth()->id();

        // Set team_id if not already set (for TeamAdmins)
        if (!isset($data['team_id']) || empty($data['team_id'])) {
            $data['team_id'] = auth()->user()->current_team_id;
        }

        // Set published_at if status is published
        if ($data['status'] === 'published' && !isset($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
