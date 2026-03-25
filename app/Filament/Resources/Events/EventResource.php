<?php

namespace App\Filament\Resources\Events;

use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\Schemas\EventForm;
use App\Filament\Resources\Events\Tables\EventsTable;
use App\Models\Event;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 15;

    public static function form(Schema $schema): Schema
    {
        return EventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'edit' => EditEvent::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // SuperAdmins can see all events, TeamAdmins can only see their team's events
        if (auth()->user()->isTeamAdmin() && ! auth()->user()->isSuperAdmin()) {
            $query->where('team_id', auth()->user()->current_team_id);
        }

        return $query;
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getEloquentQuery();
    }

    public static function canEdit($record): bool
    {
        // Imported events cannot be edited
        if ($record->isImported()) {
            return false;
        }

        return parent::canEdit($record);
    }

    public static function canDelete($record): bool
    {
        // Imported events cannot be deleted manually
        if ($record->isImported()) {
            return false;
        }

        return parent::canDelete($record);
    }

    public static function getNavigationBadge(): ?string
    {
        $query = static::getEloquentQuery();

        // Show count of upcoming published events
        $count = $query->published()
            ->upcoming()
            ->count();

        return $count > 0 ? (string) $count : null;
    }
}
