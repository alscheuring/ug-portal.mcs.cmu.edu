<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Models\Page;
use App\Models\Sidebar;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Page Content')
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
                            })
                            ->columnSpanFull(),

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
                            })
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'bulletList',
                                'orderedList',
                                'blockquote',
                                'h2',
                                'h3',
                                'h4',
                                'codeBlock',
                            ]),
                    ]),

                Section::make('Page Hierarchy')
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
                            ->helperText('Select a parent page to create a hierarchical structure'),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first in navigation'),
                    ])
                    ->columns(2),

                Section::make('Page Sidebars')
                    ->description('Select which sidebar boxes will appear on this page')
                    ->schema([
                        Select::make('sidebar_ids')
                            ->label('Sidebars')
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
                            ->placeholder('Select sidebars to display on this page')
                            ->helperText('Select multiple sidebars. They will appear in the order selected.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),

                Section::make('SEO & Metadata')
                    ->schema([
                        TextInput::make('meta_title')
                            ->maxLength(60)
                            ->helperText('Page title for search engines (leave blank to use page title)')
                            ->columnSpanFull(),

                        TextInput::make('meta_description')
                            ->maxLength(160)
                            ->helperText('Brief description for search engines')
                            ->columnSpanFull(),
                    ]),

                Section::make('Publishing')
                    ->schema([
                        Select::make('team_id')
                            ->relationship('team', 'name')
                            ->required()
                            ->visible(fn () => auth()->user()->isSuperAdmin())
                            ->helperText('SuperAdmins can choose any team, TeamAdmins are automatically assigned to their team'),

                        Hidden::make('team_id')
                            ->default(fn () => auth()->user()->isSuperAdmin() ? null : auth()->user()->current_team_id)
                            ->visible(fn () => ! auth()->user()->isSuperAdmin()),

                        Hidden::make('author_id')
                            ->default(fn () => auth()->id()),

                        Toggle::make('is_published')
                            ->label('Published')
                            ->helperText('Toggle to publish/unpublish this page'),

                        DateTimePicker::make('published_at')
                            ->label('Publish Date')
                            ->helperText('Schedule when this page should be published (leave blank for immediate)')
                            ->default(now()),
                    ])
                    ->columns(2),
            ]);
    }
}
