<?php

namespace App\Filament\Fabricator\Layouts;

use Z3d0X\FilamentFabricator\Layouts\Layout;

class DefaultLayout extends Layout
{
    protected static ?string $name = 'default';

    protected static ?string $title = 'Default Layout';

    protected static ?string $description = 'Standard layout with content area and right sidebar - perfect for most pages';
}