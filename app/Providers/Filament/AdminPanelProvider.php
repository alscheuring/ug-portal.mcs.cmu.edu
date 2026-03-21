<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\DepartmentPortalWidget;
use App\Http\Middleware\EnsureProfileCompleted;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
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
use Awcodes\Curator\CuratorPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('')
            ->brandLogo('')
            ->favicon(asset('images/mcs-logo.jpg'))
            ->login(fn () => redirect()->route('auth.google.redirect'))
            ->colors([
                'primary' => Color::Red,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                DepartmentPortalWidget::class,
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->plugin(FilamentFabricatorPlugin::make())
            ->plugin(CuratorPlugin::make())
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
            ->userMenuItems([
                'department-portal' => MenuItem::make()
                    ->label(fn (): string => auth()->user()->team ? 'View '.auth()->user()->team->name.' Portal' : 'Department Portal')
                    ->url(fn (): string => auth()->user()->team ? route('public.team.index', auth()->user()->team->slug) : '#')
                    ->icon('heroicon-o-academic-cap')
                    ->visible(fn (): bool => auth()->user()->isTeamAdmin() && auth()->user()->team)
                    ->openUrlInNewTab(),
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                function (): string {
                    if (! auth()->check()) {
                        return '';
                    }

                    $user = auth()->user();
                    $teamLink = $user->team ? route('public.team.index', $user->team->slug) : '#';
                    $teamName = $user->team ? $user->team->name : 'Department';

                    return '<style>
                        .fi-topbar { display: none !important; }
                        .fi-layout { margin-top: 60px !important; }
                        .fi-main { padding-top: 0 !important; }
                        .fi-sidebar { margin-top: 60px !important; }
                        .cmu-header {
                            position: fixed;
                            top: 0;
                            left: 0;
                            right: 0;
                            z-index: 60;
                            background: white;
                            border-bottom: 1px solid #e5e7eb;
                            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                            height: 60px;
                        }
                        .cmu-header-content {
                            max-width: 80rem;
                            margin: 0 auto;
                            padding: 0.75rem 1rem;
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            font-size: 0.875rem;
                            color: #4b5563;
                            border-bottom: 1px solid #f3f4f6;
                        }
                        .cmu-left { display: flex; align-items: center; gap: 1rem; }
                        .cmu-logo { width: 2.5rem; height: 2.5rem; border-radius: 50%; object-fit: cover; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                        .cmu-brand-text { display: flex; flex-direction: column; }
                        .cmu-brand { color: #dc2626; font-weight: 500; font-size: 0.875rem; }
                        .cmu-subtitle { color: #6b7280; font-size: 0.75rem; }
                        .cmu-user-info { display: flex; align-items: center; gap: 0.75rem; }
                        .cmu-profile-dropdown { position: relative; display: inline-block; }
                        .cmu-profile-btn {
                            display: flex;
                            align-items: center;
                            gap: 0.5rem;
                            background: none;
                            border: none;
                            color: #4b5563;
                            cursor: pointer;
                            font-size: inherit;
                            padding: 0.5rem;
                            border-radius: 0.375rem;
                        }
                        .cmu-profile-btn:hover { background: #f3f4f6; }
                        .cmu-profile-avatar {
                            width: 2rem;
                            height: 2rem;
                            border-radius: 50%;
                            background: #374151;
                            color: white;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-weight: 500;
                            font-size: 0.75rem;
                        }
                        .cmu-dropdown-menu {
                            position: absolute;
                            right: 0;
                            top: 100%;
                            background: white;
                            border: 1px solid #e5e7eb;
                            border-radius: 0.5rem;
                            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
                            min-width: 200px;
                            z-index: 70;
                            display: none;
                        }
                        .cmu-dropdown-menu.show { display: block; }
                        .cmu-dropdown-item {
                            display: block;
                            width: 100%;
                            padding: 0.75rem 1rem;
                            text-align: left;
                            color: #374151;
                            text-decoration: none;
                            border: none;
                            background: none;
                            cursor: pointer;
                            font-size: 0.875rem;
                        }
                        .cmu-dropdown-item:hover { background: #f3f4f6; }
                        .cmu-dropdown-divider { height: 1px; background: #e5e7eb; margin: 0.25rem 0; }
                        .cmu-portal-link { color: #2563eb; text-decoration: none; font-weight: 500; }
                        .cmu-portal-link:hover { color: #1d4ed8; }

                        /* Hide default Filament user avatar since we have our own in the header */
                        .fi-avatar, .fi-user-avatar { display: none !important; }

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

                        /* Page builder and form layout improvements */
                        .fi-section {
                            margin-bottom: 1.5rem;
                        }

                        .fi-section-content {
                            padding: 1.25rem;
                        }

                        /* Page builder specific styles */
                        [data-field-wrapper="blocks"] {
                            min-height: 300px;
                        }

                        .filament-fabricator-page-builder {
                            border: 1px solid rgb(209 213 219);
                            border-radius: 0.375rem;
                            padding: 1rem;
                            background: rgb(249 250 251);
                        }

                        /* Rich editor improvements */
                        .fi-fo-rich-editor .ProseMirror {
                            min-height: 150px;
                        }

                        /* Form layout improvements */
                        .fi-fo-section-header {
                            border-bottom: 1px solid rgb(229 231 235);
                            padding-bottom: 0.75rem;
                            margin-bottom: 1rem;
                        }

                        /* Mobile responsive adjustments */
                        @media (max-width: 1023px) {
                            .fi-section {
                                margin-bottom: 1rem;
                            }

                            .fi-section-content {
                                padding: 1rem;
                            }
                        }
                    </style>
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            const header = document.createElement("div");
                            header.className = "cmu-header";
                            header.innerHTML = `
                                <div class="cmu-header-content">
                                    <div class="cmu-left">
                                        <img src="/images/mcs-logo.jpg" alt="MCS Logo" class="cmu-logo">
                                        <div class="cmu-brand-text">
                                            <div class="cmu-brand">Carnegie Mellon University • Mellon College of Science</div>
                                            <div class="cmu-subtitle">'.config('app.name').' - Admin</div>
                                        </div>
                                    </div>
                                    <div class="cmu-user-info">
                                        <div class="cmu-profile-dropdown">
                                            <button class="cmu-profile-btn" onclick="toggleProfileMenu()">
                                                <div class="cmu-profile-avatar">'.strtoupper(substr(e($user->name), 0, 2)).'</div>
                                                <span>'.e($user->name).'</span>
                                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                                </svg>
                                            </button>
                                            <div class="cmu-dropdown-menu" id="profile-dropdown">
                                                '.($user->team ? '<a href="'.e($teamLink).'" class="cmu-dropdown-item cmu-portal-link" target="_blank">'.e($teamName).' Portal</a><div class="cmu-dropdown-divider"></div>' : '').'
                                                <form method="POST" action="'.route('safe-logout').'" style="margin: 0;">
                                                    <input type="hidden" name="_token" value="'.csrf_token().'">
                                                    <button type="submit" class="cmu-dropdown-item">Sign out</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                            // Add dropdown functionality
                            window.toggleProfileMenu = function() {
                                const dropdown = document.getElementById("profile-dropdown");
                                dropdown.classList.toggle("show");
                            };

                            // Close dropdown when clicking outside
                            document.addEventListener("click", function(event) {
                                const dropdown = document.getElementById("profile-dropdown");
                                const button = event.target.closest(".cmu-profile-btn");
                                if (!button && dropdown) {
                                    dropdown.classList.remove("show");
                                }
                            });
                            document.body.insertBefore(header, document.body.firstChild);

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
                    </script>';
                }
            );
    }
}
