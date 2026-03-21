<?php

namespace App\Providers\Filament;

use App\Http\Middleware\EnsureProfileCompleted;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Z3d0X\FilamentFabricator\FilamentFabricatorPlugin;

class StudentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('student')
            ->path('student')
            ->brandName('CMU Student Portal')
            ->brandLogo(asset('images/cmu-logo.svg'))
            ->brandLogoHeight('2rem')
            ->favicon(asset('images/cmu-favicon.ico'))
            ->login(fn () => redirect()->route('auth.google.redirect'))
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Student/Resources'), for: 'App\Filament\Student\Resources')
            ->discoverPages(in: app_path('Filament/Student/Pages'), for: 'App\Filament\Student\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Student/Widgets'), for: 'App\Filament\Student\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->plugin(FilamentFabricatorPlugin::make())
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                EnsureProfileCompleted::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<style>
                        /* Position notifications in bottom right corner */
                        .fi-notifications {
                            position: fixed !important;
                            bottom: 1rem !important;
                            right: 1rem !important;
                            top: auto !important;
                            left: auto !important;
                            z-index: 2147483647 !important;
                            max-width: 400px !important;
                        }

                        /* Style individual notification items */
                        .fi-notification {
                            margin-bottom: 0.5rem !important;
                        }

                        /* Ensure notifications appear above header */
                        .fi-layout .fi-notifications,
                        .fi-main .fi-notifications {
                            position: fixed !important;
                            bottom: 1rem !important;
                            right: 1rem !important;
                            z-index: 2147483647 !important;
                        }
                    </style>
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            // Minimal JavaScript - only ensure z-index without moving elements
                            function ensureNotificationVisibility() {
                                document.querySelectorAll(".fi-notifications").forEach(notification => {
                                    // Only apply z-index fix, don\'t move or manipulate content
                                    notification.style.setProperty("z-index", "2147483647", "important");
                                });
                            }

                            // Run once after page load
                            setTimeout(ensureNotificationVisibility, 500);
                        });
                    </script>'
            );
    }
}
