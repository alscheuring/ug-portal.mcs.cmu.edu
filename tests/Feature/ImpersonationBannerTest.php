<?php

it('validates impersonation banner component has correct z-index', function () {
    // Read the component file directly since it won't render without active impersonation
    $componentContent = file_get_contents(resource_path('views/components/impersonate-banner.blade.php'));

    // Should contain the updated z-index (70 instead of 9999)
    expect($componentContent)->toContain('z-index: 70;');

    // Should not contain the old problematic z-index
    expect($componentContent)->not->toContain('z-index: 9999;');
});

it('validates admin panel provider has correct sidebar spacing', function () {
    // Test that the provider contains the positioning logic
    $providerContent = file_get_contents(app_path('Providers/Filament/AdminPanelProvider.php'));

    // Should contain the impersonation check
    expect($providerContent)->toContain('$isImpersonating = Impersonation::isImpersonating();');

    // Should contain the dynamic positioning variables with reduced sidebar margin
    expect($providerContent)->toContain('$headerTop = $isImpersonating ? \'50px\' : \'0\';');
    expect($providerContent)->toContain('$layoutMarginTop = $isImpersonating ? \'110px\' : \'60px\';');
    expect($providerContent)->toContain('$sidebarMarginTop = $isImpersonating ? \'70px\' : \'20px\';');

    // Should use the dynamic variables in CSS
    expect($providerContent)->toContain('top: \'.$headerTop.\';');
    expect($providerContent)->toContain('margin-top: \'.$layoutMarginTop.\' !important;');
    expect($providerContent)->toContain('margin-top: \'.$sidebarMarginTop.\' !important;');
});

it('validates sidebar margin was reduced from original 60px to 20px', function () {
    // This test ensures we actually reduced the excessive spacing
    $providerContent = file_get_contents(app_path('Providers/Filament/AdminPanelProvider.php'));

    // Should NOT contain the old excessive spacing
    expect($providerContent)->not->toContain('$sidebarMarginTop = $isImpersonating ? \'110px\' : \'60px\';');
    expect($providerContent)->not->toContain('$sidebarMarginTop = $isImpersonating ? \'90px\' : \'40px\';');

    // Should contain the new minimal spacing
    expect($providerContent)->toContain('$sidebarMarginTop = $isImpersonating ? \'70px\' : \'20px\';');
});
