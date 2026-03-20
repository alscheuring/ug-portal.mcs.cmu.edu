<?php

namespace App\Filament\Resources\Announcements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state))),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->alphaDash()
                    ->helperText('URL-friendly version of the title'),

                Textarea::make('excerpt')
                    ->maxLength(500)
                    ->helperText('Brief summary of the announcement (optional)'),

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
                    ]),

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
                    ->helperText('Toggle to publish/unpublish this announcement'),

                DateTimePicker::make('published_at')
                    ->label('Publish Date')
                    ->helperText('Schedule when this announcement should be published (leave blank for immediate)')
                    ->default(now()),
            ]);
    }
}
