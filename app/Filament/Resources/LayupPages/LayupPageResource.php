<?php

namespace App\Filament\Resources\LayupPages;

use App\Filament\Resources\LayupPages\Pages\CreateLayupPage;
use App\Filament\Resources\LayupPages\Pages\EditLayupPage;
use App\Filament\Resources\LayupPages\Pages\ListLayupPages;
use App\Models\Sidebar;
use Crumbls\Layup\Resources\PageResource;
use Filament\Forms\Components\CheckboxList;
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

                $existingSchema[] = CheckboxList::make('sidebars')
                    ->label('Sidebars')
                    ->relationship('sidebars', 'title')
                    ->getOptionLabelFromRecordUsing(function (Sidebar $record): string {
                        return "{$record->title} ({$record->name})";
                    })
                    ->options(function (callable $get) {
                        $teamId = $get('team_id') ?? auth()->user()->current_team_id;

                        if (! $teamId) {
                            return [];
                        }

                        return Sidebar::where('team_id', $teamId)
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->pluck('title', 'id')
                            ->toArray();
                    })
                    ->descriptions(function (callable $get) {
                        $teamId = $get('team_id') ?? auth()->user()->current_team_id;

                        if (! $teamId) {
                            return [];
                        }

                        return Sidebar::where('team_id', $teamId)
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->mapWithKeys(function ($name, $id) {
                                return [$id => "Internal name: {$name}"];
                            })
                            ->toArray();
                    })
                    ->columns(2)
                    ->helperText('Select which sidebars should be displayed on this page. Only active sidebars from the same team are shown.')
                    ->visible(function (callable $get) {
                        $teamId = $get('team_id') ?? auth()->user()->current_team_id;

                        return $teamId && Sidebar::where('team_id', $teamId)->where('is_active', true)->exists();
                    });

                $component->schema($existingSchema);
                break;
            }
        }

        return $schema;
    }

    public static function table(Table $table): Table
    {
        $table = parent::table($table);

        $columns = $table->getColumns();

        // Add team column if user is SuperAdmin
        if (auth()->user()?->isSuperAdmin()) {
            $columns[] = TextColumn::make('team.name')
                ->label('Team')
                ->sortable()
                ->searchable();
        }

        // Add sidebars column
        $columns[] = TextColumn::make('sidebars.title')
            ->label('Sidebars')
            ->badge()
            ->separator(',')
            ->limit(50)
            ->tooltip(function ($record) {
                if ($record->sidebars->isEmpty()) {
                    return 'No sidebars assigned';
                }

                return $record->sidebars->pluck('title')->join(', ');
            })
            ->default('—')
            ->color('gray');

        $table->columns($columns);

        return $table;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Eager load relationships to prevent N+1 queries
        $query->with(['sidebars', 'team']);

        // SuperAdmins can see all pages, TeamAdmins can only see their team's pages
        if (auth()->user()?->isTeamAdmin() && ! auth()->user()?->isSuperAdmin()) {
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
