<?php

namespace App\Filament\Resources\EventFeeds;

use App\Filament\Resources\EventFeeds\Pages\CreateEventFeed;
use App\Filament\Resources\EventFeeds\Pages\EditEventFeed;
use App\Filament\Resources\EventFeeds\Pages\ListEventFeeds;
use App\Filament\Resources\EventFeeds\Schemas\EventFeedForm;
use App\Filament\Resources\EventFeeds\Tables\EventFeedsTable;
use App\Models\EventFeed;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventFeedResource extends Resource
{
    protected static ?string $model = EventFeed::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rss';

    protected static ?int $navigationSort = 16;

    protected static ?string $navigationLabel = 'Event Feeds';

    protected static ?string $modelLabel = 'Event Feed';

    protected static ?string $pluralModelLabel = 'Event Feeds';

    public static function form(Schema $schema): Schema
    {
        return EventFeedForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventFeedsTable::configure($table);
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
            'index' => ListEventFeeds::route('/'),
            'create' => CreateEventFeed::route('/create'),
            'edit' => EditEventFeed::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // SuperAdmins can see all event feeds, TeamAdmins can only see their team's feeds
        if (auth()->user()->isTeamAdmin() && ! auth()->user()->isSuperAdmin()) {
            $query->where('team_id', auth()->user()->current_team_id);
        }

        return $query;
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getEloquentQuery();
    }

    public static function getNavigationBadge(): ?string
    {
        $query = static::getEloquentQuery();

        // Show count of active feeds that need import
        $count = $query->active()
            ->needingImport()
            ->count();

        return $count > 0 ? (string) $count : null;
    }
}
