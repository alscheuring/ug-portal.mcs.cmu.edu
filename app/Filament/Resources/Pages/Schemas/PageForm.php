<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\Page;
use App\Models\Sidebar;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Z3d0X\FilamentFabricator\Forms\Components\PageBuilder;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns([
                'default' => 1,
                'lg' => 2,
            ])
            ->components([
                // Left Column - Main Content
                Section::make('Basic Information')
                    ->icon('heroicon-m-document-text')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set, $get) {
                                if (! $get('slug')) {
                                    $teamId = auth()->user()->isSuperAdmin()
                                        ? $get('team_id')
                                        : auth()->user()->current_team_id;

                                    if ($teamId) {
                                        $slug = Page::generateSlug($state, $teamId);
                                        $set('slug', $slug);
                                    }
                                }
                            }),

                        TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->alphaDash()
                            ->helperText('URL-friendly version of the title')
                            ->unique(Page::class, 'slug', ignoreRecord: true, modifyRuleUsing: function ($rule, $get) {
                                $teamId = auth()->user()->isSuperAdmin()
                                    ? $get('team_id')
                                    : auth()->user()->current_team_id;

                                return $rule->where('team_id', $teamId);
                            }),
                    ])
                    ->columns(1)
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 2,
                    ]),

                Section::make('Page Content')
                    ->icon('heroicon-m-squares-plus')
                    ->description('Build your page using the drag-and-drop page builder.')
                    ->schema([
                        PageBuilder::make('blocks')
                            ->label('Page Builder')
                            ->helperText('Drag and drop components to build your page layout'),
                    ])
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 2,
                    ]),

                // Right Column - Settings & Metadata
                Section::make('Publishing Settings')
                    ->icon('heroicon-m-eye')
                    ->schema([
                        Select::make('team_id')
                            ->relationship('team', 'name')
                            ->required()
                            ->visible(fn () => auth()->user()->isSuperAdmin())
                            ->helperText('Choose the team this page belongs to'),

                        Hidden::make('team_id')
                            ->default(fn () => auth()->user()->isSuperAdmin() ? null : auth()->user()->current_team_id)
                            ->visible(fn () => ! auth()->user()->isSuperAdmin()),

                        Hidden::make('author_id')
                            ->default(fn () => auth()->id()),

                        Toggle::make('is_published')
                            ->label('Published')
                            ->helperText('Make this page visible to users')
                            ->inline(false),

                        DateTimePicker::make('published_at')
                            ->label('Publish Date')
                            ->helperText('Schedule when this page should go live')
                            ->default(now())
                            ->native(false),
                    ])
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 1,
                    ]),

                Section::make('Page Organization')
                    ->icon('heroicon-m-folder-open')
                    ->collapsible()
                    ->schema([
                        Select::make('parent_id')
                            ->label('Parent Page')
                            ->relationship('parent', 'title')
                            ->searchable()
                            ->options(function () {
                                $teamId = auth()->user()->isSuperAdmin()
                                    ? request()->get('team_id')
                                    : auth()->user()->current_team_id;

                                if (! $teamId) {
                                    return [];
                                }

                                return Page::forTeam($teamId)
                                    ->orderBy('title')
                                    ->pluck('title', 'id');
                            })
                            ->helperText('Create a hierarchical page structure'),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first in navigation'),
                    ])
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 1,
                    ]),

                Section::make('Page Sidebars')
                    ->icon('heroicon-m-view-columns')
                    ->description('Manage sidebar components for this page')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Select::make('sidebar_ids')
                            ->label('Active Sidebars')
                            ->multiple()
                            ->options(function ($get) {
                                $teamId = auth()->user()->isSuperAdmin()
                                    ? $get('team_id')
                                    : auth()->user()->current_team_id;

                                if (! $teamId) {
                                    return [];
                                }

                                return Sidebar::where('team_id', $teamId)
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->placeholder('Choose sidebars to display')
                            ->helperText('Sidebars appear in the order selected'),
                    ])
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 1,
                    ]),

                Section::make('SEO & Metadata')
                    ->icon('heroicon-m-magnifying-glass')
                    ->description('Optimize this page for search engines')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('SEO Title')
                            ->maxLength(60)
                            ->helperText('Page title for search engines (60 chars max)')
                            ->suffixIcon('heroicon-m-hashtag'),

                        TextInput::make('meta_description')
                            ->label('SEO Description')
                            ->maxLength(160)
                            ->helperText('Brief description for search engines (160 chars max)')
                            ->suffixIcon('heroicon-m-document-text'),
                    ])
                    ->columnSpan([
                        'default' => 1,
                        'lg' => 1,
                    ]),
            ]);
    }
}
