<?php

namespace App\Filament\Fabricator\PageBlocks;

use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Z3d0X\FilamentFabricator\PageBlocks\PageBlock;

class TextBlock extends PageBlock
{
    protected static string $name = 'text';

    public static function defineBlock(Block $block): Block
    {
        return $block
            ->schema([
                TextInput::make('heading')
                    ->label('Heading')
                    ->placeholder('Enter a heading for this text block'),

                RichEditor::make('content')
                    ->label('Content')
                    ->required()
                    ->toolbarButtons([
                        'bold',
                        'italic',
                        'underline',
                        'link',
                        'bulletList',
                        'orderedList',
                        'blockquote',
                        'h2',
                        'h3',
                    ]),
            ]);
    }

    public static function mutateData(array $data): array
    {
        return $data;
    }
}
