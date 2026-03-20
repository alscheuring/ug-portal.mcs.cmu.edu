<?php

namespace App\Filament\Resources\Teams\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();

        return $schema
            ->columns(1)
            ->components([
                Section::make('Team Information')
                    ->schema(array_filter([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->disabled(! $isSuperAdmin),

                        $isSuperAdmin ? TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('URL-friendly version of the team name') : null,

                        TextInput::make('manager_email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->disabled(! $isSuperAdmin),

                        Textarea::make('description')
                            ->rows(3),

                        $isSuperAdmin ? Toggle::make('is_active')
                            ->default(true) : null,
                    ])),

                Section::make('Contact Information')
                    ->description('Customize the "Get in Touch" section on your team portal')
                    ->schema([
                        TextInput::make('contact_title')
                            ->label('Contact Section Title')
                            ->maxLength(255)
                            ->default('Get in Touch')
                            ->helperText('Title for the contact section (defaults to "Get in Touch")'),

                        RichEditor::make('contact_content')
                            ->label('Contact Information')
                            ->helperText('Use the rich text editor to format your contact information. You can add headings, links, bold text, etc.')
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
                    ])
                    ->collapsible(),

            ]);
    }
}
