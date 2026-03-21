<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class ImageBlock extends PageBlock
{
    protected static string $name = 'image';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                FileUpload::make('image')
                    ->label('Image')
                    ->required()
                    ->image()
                    ->directory('page-images'),

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
