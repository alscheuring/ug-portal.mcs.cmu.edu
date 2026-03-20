<?php

namespace App\Filament\Resources\Sidebars\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SidebarForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Sidebar Information')
                    ->schema([
                        Hidden::make('team_id')
                            ->default(fn () => auth()->user()->current_team_id),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Internal name for organizing sidebars (not shown to users)'),

                        TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->helperText('Title displayed at the top of the sidebar box'),

                        RichEditor::make('content')
                            ->required()
                            ->helperText('Content displayed in the sidebar box. You can include links, formatting, and HTML.')
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'link',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'blockquote',
                                'codeBlock',
                            ]),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive sidebars will not be displayed on any pages'),
                    ]),
            ]);
    }
}
