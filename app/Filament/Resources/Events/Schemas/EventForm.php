<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Event Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn ($state, $set) => $set('slug', Str::slug($state)))
                                    ->disabled(fn (?object $record) => $record?->isImported()),

                                TextInput::make('slug')
                                    ->required()
                                    ->maxLength(255)
                                    ->unique(ignoreRecord: true)
                                    ->alphaDash()
                                    ->helperText('URL-friendly version of the title')
                                    ->disabled(fn (?object $record) => $record?->isImported()),
                            ]),

                        Textarea::make('summary')
                            ->maxLength(500)
                            ->helperText('Brief summary of the event (optional)')
                            ->disabled(fn (?object $record) => $record?->isImported()),

                        RichEditor::make('description')
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
                            ])
                            ->disabled(fn (?object $record) => $record?->isImported()),
                    ]),

                Section::make('Date & Time')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('start_datetime')
                                    ->label('Start Date & Time')
                                    ->required()
                                    ->seconds(false)
                                    ->live()
                                    ->disabled(fn (?object $record) => $record?->isImported()),

                                DateTimePicker::make('end_datetime')
                                    ->label('End Date & Time')
                                    ->required()
                                    ->seconds(false)
                                    ->after('start_datetime')
                                    ->disabled(fn (?object $record) => $record?->isImported()),
                            ]),

                        Placeholder::make('duration')
                            ->label('Duration')
                            ->content(function (Get $get): string {
                                $start = $get('start_datetime');
                                $end = $get('end_datetime');

                                if (! $start || ! $end) {
                                    return 'Select start and end times to see duration';
                                }

                                try {
                                    $startDate = new \DateTime($start);
                                    $endDate = new \DateTime($end);
                                    $interval = $startDate->diff($endDate);

                                    if ($interval->days > 0) {
                                        return $interval->format('%d day(s), %h hour(s), %i minute(s)');
                                    } elseif ($interval->h > 0) {
                                        return $interval->format('%h hour(s), %i minute(s)');
                                    } else {
                                        return $interval->format('%i minute(s)');
                                    }
                                } catch (\Exception $e) {
                                    return 'Invalid date format';
                                }
                            }),
                    ]),

                Section::make('Location & Links')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('location')
                                    ->maxLength(255)
                                    ->helperText('Physical location or "Online Event"')
                                    ->disabled(fn (?object $record) => $record?->isImported()),

                                TextInput::make('info_url')
                                    ->label('Information URL')
                                    ->url()
                                    ->maxLength(255)
                                    ->helperText('Link to event details page')
                                    ->disabled(fn (?object $record) => $record?->isImported()),
                            ]),

                        TextInput::make('image_url')
                            ->label('Image URL')
                            ->url()
                            ->maxLength(255)
                            ->helperText('URL to event image/poster')
                            ->disabled(fn (?object $record) => $record?->isImported()),
                    ]),

                Section::make('Tags & Organization')
                    ->schema([
                        TagsInput::make('tags')
                            ->helperText('Add tags to categorize this event')
                            ->suggestions([
                                'academic',
                                'research',
                                'seminar',
                                'workshop',
                                'conference',
                                'social',
                                'networking',
                                'graduation',
                                'orientation',
                                'meeting',
                                'lecture',
                                'presentation',
                                'competition',
                                'career',
                                'volunteer',
                                'student',
                                'faculty',
                                'undergraduate',
                                'graduate',
                                'phd',
                            ])
                            ->disabled(fn (?object $record) => $record?->isImported()),

                        Select::make('team_id')
                            ->relationship('team', 'name')
                            ->required()
                            ->visible(fn () => auth()->user()->isSuperAdmin())
                            ->helperText('SuperAdmins can choose any team, TeamAdmins are automatically assigned to their team')
                            ->disabled(fn (?object $record) => $record?->isImported()),

                        Hidden::make('team_id')
                            ->default(fn () => auth()->user()->isSuperAdmin() ? null : auth()->user()->current_team_id)
                            ->visible(fn () => ! auth()->user()->isSuperAdmin()),

                        Hidden::make('author_id')
                            ->default(fn () => auth()->id())
                            ->visible(fn (?object $record) => ! $record?->isImported()),
                    ]),

                Section::make('Publishing')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Toggle::make('is_published')
                                    ->label('Published')
                                    ->helperText('Toggle to publish/unpublish this event')
                                    ->default(true)
                                    ->disabled(fn (?object $record) => $record?->isImported()),

                                Placeholder::make('source_info')
                                    ->label('Source')
                                    ->content(fn (?object $record): string => match (true) {
                                        $record?->isImported() => 'Imported from: '.$record->eventFeed?->name ?? 'External Feed',
                                        $record?->isManual() => 'Manually created by: '.$record->author?->name ?? 'System',
                                        default => 'Manual event'
                                    })
                                    ->visible(fn (?object $record) => $record !== null),
                            ]),

                        Placeholder::make('import_warning')
                            ->content('⚠️ This event was imported from an external feed and cannot be edited. Changes will be overwritten on the next import.')
                            ->visible(fn (?object $record) => $record?->isImported())
                            ->extraAttributes(['class' => 'text-warning-600 font-medium']),
                    ]),
            ]);
    }
}
