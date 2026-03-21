<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;
use Awcodes\Curator\Components\Forms\CuratorPicker;

class ImageBlock extends PageBlock
{
    protected static string $name = 'image';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                CuratorPicker::make('image')
                    ->label('Image')
                    ->required()
                    ->buttonLabel('Select Image')
                    ->helperText('Choose from the media library or upload a new image'),

                TextInput::make('alt_text')
                    ->label('Alt Text')
                    ->required()
                    ->placeholder('Describe the image for accessibility'),

                TextInput::make('caption')
                    ->label('Caption')
                    ->placeholder('Optional image caption'),

                Select::make('alignment')
                    ->label('Image Alignment')
                    ->options([
                        'left' => 'Left',
                        'center' => 'Center',
                        'right' => 'Right',
                        'full' => 'Full Width',
                    ])
                    ->default('center'),

                Select::make('size')
                    ->label('Image Size')
                    ->options([
                        'small' => 'Small',
                        'medium' => 'Medium',
                        'large' => 'Large',
                        'original' => 'Original Size',
                    ])
                    ->default('medium'),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
