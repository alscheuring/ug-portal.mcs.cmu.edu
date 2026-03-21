<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Awcodes\Curator\Components\Forms\CuratorPicker;

class HeroBlock extends PageBlock
{
    protected static string $name = 'hero';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                TextInput::make('title')
                    ->label('Hero Title')
                    ->required()
                    ->placeholder('Enter the main hero title'),

                Textarea::make('subtitle')
                    ->label('Hero Subtitle')
                    ->placeholder('Enter a subtitle or description')
                    ->rows(2),

                TextInput::make('button_text')
                    ->label('Button Text')
                    ->placeholder('Call to Action'),

                TextInput::make('button_link')
                    ->label('Button Link')
                    ->placeholder('https://example.com or /internal-page')
                    ->url(),

                CuratorPicker::make('background_image')
                    ->label('Background Image')
                    ->buttonLabel('Select Background')
                    ->helperText('Choose a background image for the hero section'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
