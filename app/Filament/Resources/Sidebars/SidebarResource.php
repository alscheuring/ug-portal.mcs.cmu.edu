<?php

namespace App\Filament\Resources\Sidebars;

use App\Filament\Resources\Sidebars\Pages\CreateSidebar;
use App\Filament\Resources\Sidebars\Pages\EditSidebar;
use App\Filament\Resources\Sidebars\Pages\ListSidebars;
use App\Filament\Resources\Sidebars\Schemas\SidebarForm;
use App\Filament\Resources\Sidebars\Tables\SidebarsTable;
use App\Models\Sidebar;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SidebarResource extends Resource
{
    protected static ?string $model = Sidebar::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Content Management';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return SidebarForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SidebarsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        // SuperAdmins can see all sidebars
        if ($user->isSuperAdmin()) {
            return $query;
        }

        // TeamAdmins can only see their own team's sidebars
        if ($user->isTeamAdmin() && $user->current_team_id) {
            return $query->where('team_id', $user->current_team_id);
        }

        // Students cannot access sidebars
        return $query->where('id', null); // Return empty query
    }

    public static function canCreate(): bool
    {
        // Only SuperAdmins and TeamAdmins can create sidebars
        return auth()->user()->isSuperAdmin() || auth()->user()->isTeamAdmin();
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
            'index' => ListSidebars::route('/'),
            'create' => CreateSidebar::route('/create'),
            'edit' => EditSidebar::route('/{record}/edit'),
        ];
    }
}
