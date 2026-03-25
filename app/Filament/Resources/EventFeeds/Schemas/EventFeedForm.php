<?php

namespace App\Filament\Resources\EventFeeds\Schemas;

use App\Models\EventFeed;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

class EventFeedForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Feed Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Descriptive name for this event feed'),

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->helperText('Active feeds are imported automatically')
                                    ->default(true),
                            ]),

                        TextInput::make('api_url')
                            ->label('API URL')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->helperText('URL of the external events API or calendar feed')
                            ->suffixAction(
                                Action::make('test_connection')
                                    ->label('Test Connection')
                                    ->icon('heroicon-o-bolt')
                                    ->action(function (Get $get) {
                                        $url = $get('api_url');

                                        if (! $url) {
                                            Notification::make()
                                                ->warning()
                                                ->title('No URL provided')
                                                ->body('Please enter an API URL to test')
                                                ->send();

                                            return;
                                        }

                                        $feed = new EventFeed(['api_url' => $url]);
                                        $result = $feed->testConnection();

                                        if ($result['success']) {
                                            Notification::make()
                                                ->success()
                                                ->title('Connection successful!')
                                                ->body($result['message'])
                                                ->send();
                                        } else {
                                            Notification::make()
                                                ->danger()
                                                ->title('Connection failed')
                                                ->body($result['message'])
                                                ->send();
                                        }
                                    })
                            ),

                        TextInput::make('max_events')
                            ->label('Maximum Events')
                            ->numeric()
                            ->default(50)
                            ->minValue(1)
                            ->maxValue(500)
                            ->helperText('Maximum number of events to import from this feed'),

                        Select::make('team_id')
                            ->relationship('team', 'name')
                            ->required()
                            ->visible(fn () => auth()->user()->isSuperAdmin())
                            ->helperText('SuperAdmins can choose any team, TeamAdmins are automatically assigned to their team'),

                        Hidden::make('team_id')
                            ->default(fn () => auth()->user()->isSuperAdmin() ? null : auth()->user()->current_team_id)
                            ->visible(fn () => ! auth()->user()->isSuperAdmin()),
                    ]),

                Section::make('Import Configuration')
                    ->description('Configure how events are mapped from the external feed')
                    ->schema([
                        Select::make('feed_type')
                            ->label('Feed Type')
                            ->options([
                                'cmu_events' => 'CMU Events API',
                                'generic_json' => 'Generic JSON Feed',
                                'university_calendar' => 'University Calendar',
                                'custom' => 'Custom Mapping',
                            ])
                            ->default('generic_json')
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                $settings = match ($state) {
                                    'cmu_events' => EventFeed::getCmuEventsFeedSettings(),
                                    'generic_json' => EventFeed::getGenericJsonFeedSettings(),
                                    default => EventFeed::getGenericJsonFeedSettings()
                                };

                                $set('import_settings', $settings);
                            })
                            ->helperText('Select a preset configuration or choose custom for manual mapping'),

                        KeyValue::make('import_settings')
                            ->label('Field Mapping')
                            ->keyLabel('Event Field')
                            ->valueLabel('API Field')
                            ->helperText('Map event fields to corresponding fields in the API response')
                            ->default(EventFeed::getGenericJsonFeedSettings()),
                    ]),

                Section::make('Status Information')
                    ->visible(fn (?object $record) => $record !== null)
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Placeholder::make('last_imported')
                                    ->label('Last Import')
                                    ->content(fn (?object $record): string => $record?->last_imported_diff ?? 'Never imported'
                                    ),

                                Placeholder::make('import_status')
                                    ->label('Status')
                                    ->content(fn (?object $record): string => $record?->import_status ?? 'Unknown'
                                    ),

                                Placeholder::make('imported_events_count')
                                    ->label('Imported Events')
                                    ->content(fn (?object $record): string => (string) ($record?->imported_events_count ?? 0)
                                    ),
                            ]),
                    ]),

                Section::make('Quick Setup')
                    ->description('Use these presets for common feed types')
                    ->collapsed()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('cmu_preset')
                                    ->label('CMU Events API')
                                    ->content('Use this preset for CMU department events feeds')
                                    ->suffixAction(
                                        Action::make('use_cmu_preset')
                                            ->label('Use Preset')
                                            ->action(function ($set) {
                                                $set('feed_type', 'cmu_events');
                                                $set('import_settings', EventFeed::getCmuEventsFeedSettings());

                                                Notification::make()
                                                    ->success()
                                                    ->title('CMU Events preset applied')
                                                    ->send();
                                            })
                                    ),

                                Placeholder::make('generic_preset')
                                    ->label('Generic JSON')
                                    ->content('Use this preset for standard JSON calendar feeds')
                                    ->suffixAction(
                                        Action::make('use_generic_preset')
                                            ->label('Use Preset')
                                            ->action(function ($set) {
                                                $set('feed_type', 'generic_json');
                                                $set('import_settings', EventFeed::getGenericJsonFeedSettings());

                                                Notification::make()
                                                    ->success()
                                                    ->title('Generic JSON preset applied')
                                                    ->send();
                                            })
                                    ),
                            ]),
                    ]),
            ]);
    }
}
