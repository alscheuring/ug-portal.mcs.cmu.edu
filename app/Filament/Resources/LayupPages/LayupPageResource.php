<?php

namespace App\Filament\Resources\LayupPages;

use App\Filament\Resources\LayupPages\Pages\CreateLayupPage;
use App\Filament\Resources\LayupPages\Pages\EditLayupPage;
use App\Filament\Resources\LayupPages\Pages\ListLayupPages;
use Crumbls\Layup\Resources\PageResource;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LayupPageResource extends PageResource
{
    protected static ?string $navigationLabel = 'Pages';

    protected static ?string $slug = 'layup-pages';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        $schema = parent::form($schema);

        // Add team and author fields to the page details section
        $components = $schema->getComponents();

        // Find the page details section and add our fields
        foreach ($components as $component) {
            if ($component instanceof Section && $component->getLabel() === __('layup::resource.page_details')) {
                $existingSchema = $component->getSchema();
                $existingSchema[] = Select::make('team_id')
                    ->label('Team')
                    ->relationship('team', 'name')
                    ->required()
                    ->default(auth()->user()->current_team_id ?? null)
                    ->visible(fn () => auth()->user()->isSuperAdmin());

                $component->schema($existingSchema);
                break;
            }
        }

        return $schema;
    }

    public static function table(Table $table): Table
    {
        $table = parent::table($table);

        // Add team column if user is SuperAdmin
        if (auth()->user()?->isSuperAdmin()) {
            $columns = $table->getColumns();
            $columns[] = TextColumn::make('team.name')
                ->label('Team')
                ->sortable()
                ->searchable();

            $table->columns($columns);
        }

        return $table;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // SuperAdmins can see all pages, TeamAdmins can only see their team's pages
        if (auth()->user()?->isTeamAdmin() && !auth()->user()?->isSuperAdmin()) {
            $query->where('team_id', auth()->user()->current_team_id);
        }

        return $query;
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return static::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLayupPages::route('/'),
            'create' => CreateLayupPage::route('/create'),
            'edit' => EditLayupPage::route('/{record}/edit'),
        ];
    }
}
