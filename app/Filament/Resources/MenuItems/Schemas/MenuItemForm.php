<?php

namespace App\Filament\Resources\MenuItems\Schemas;

use App\Models\LayupPage;
use App\Models\Menu;
use App\Models\MenuItem;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MenuItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Menu Item Details')
                    ->schema([
                        Select::make('menu_id')
                            ->label('Menu')
                            ->required()
                            ->options(function () {
                                if (auth()->user()->isSuperAdmin()) {
                                    return Menu::with('team')->get()->pluck('name', 'id');
                                }

                                return Menu::where('team_id', auth()->user()->current_team_id)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($set) {
                                $set('parent_id', null);
                                $set('page_id', null);
                            }),

                        Select::make('parent_id')
                            ->label('Parent Menu Item')
                            ->options(function (Get $get) {
                                $menuId = $get('menu_id');
                                if (! $menuId) {
                                    return [];
                                }

                                return MenuItem::where('menu_id', $menuId)
                                    ->whereNull('parent_id')
                                    ->pluck('title', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->helperText('Leave blank for top-level menu items')
                            ->visible(fn (Get $get) => filled($get('menu_id'))),

                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->helperText('The text that will be displayed in the menu'),

                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->helperText('Lower numbers appear first in the menu')
                            ->minValue(0)
                            ->maxValue(999),
                    ])
                    ->columns(2),

                Section::make('Link Configuration')
                    ->schema([
                        Select::make('link_type')
                            ->label('Link Type')
                            ->required()
                            ->options([
                                'page' => 'Internal Page',
                                'external' => 'External URL',
                                'announcements' => 'News/Announcements',
                                'polls' => 'Polls',
                                'parent' => 'Dropdown Parent (No Link)',
                                'divider' => 'Divider (No Link)',
                            ])
                            ->default('page')
                            ->live()
                            ->columnSpanFull(),

                        Select::make('page_id')
                            ->label('Page')
                            ->required(fn (Get $get) => $get('link_type') === 'page')
                            ->options(function (Get $get) {
                                $menuId = $get('menu_id');
                                if (! $menuId) {
                                    return [];
                                }

                                $menu = Menu::find($menuId);
                                if (! $menu) {
                                    return [];
                                }

                                return LayupPage::where('team_id', $menu->team_id)
                                    ->where('status', 'published')
                                    ->orderBy('title')
                                    ->pluck('title', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->helperText('Select a page from your team')
                            ->visible(fn (Get $get) => $get('link_type') === 'page')
                            ->columnSpanFull(),

                        TextInput::make('external_url')
                            ->label('External URL')
                            ->required(fn (Get $get) => $get('link_type') === 'external')
                            ->url()
                            ->placeholder('https://example.com')
                            ->helperText('Enter the full URL including http:// or https://')
                            ->visible(fn (Get $get) => $get('link_type') === 'external')
                            ->columnSpanFull(),

                        Toggle::make('opens_in_new_tab')
                            ->label('Open in New Tab')
                            ->default(false)
                            ->helperText('External links will open in a new browser tab')
                            ->visible(fn (Get $get) => $get('link_type') === 'external'),
                    ]),

                Section::make('Display Options')
                    ->schema([
                        Toggle::make('is_visible')
                            ->label('Visible')
                            ->default(true)
                            ->helperText('Hidden items will not appear in the menu'),

                        Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500)
                            ->rows(2)
                            ->helperText('Optional description for this menu item')
                            ->columnSpanFull(),

                        TextInput::make('css_class')
                            ->label('CSS Class')
                            ->maxLength(255)
                            ->helperText('Optional CSS class for custom styling'),

                        TextInput::make('icon')
                            ->label('Icon')
                            ->maxLength(255)
                            ->helperText('Optional icon name (e.g., heroicon-o-home)'),
                    ])
                    ->columns(2),
            ]);
    }
}
