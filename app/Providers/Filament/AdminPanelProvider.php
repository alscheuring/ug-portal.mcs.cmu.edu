<?php

namespace App\Providers\Filament;

use App\Filament\Plugins\CustomLayupPlugin;
use App\Filament\Widgets\DepartmentPortalWidget;
use App\Http\Middleware\EnsureProfileCompleted;
use Awcodes\Curator\CuratorPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Notifications\Livewire\Notifications;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use STS\FilamentImpersonate\Facades\Impersonation;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Configure notification positioning - bottom right corner
        Notifications::alignment(Alignment::End);
        Notifications::verticalAlignment(VerticalAlignment::End);

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
            ->plugin(CustomLayupPlugin::make())
            ->plugin(
                CuratorPlugin::make()
                    ->label('Media')
                    ->pluralLabel('Media')
                    ->navigationIcon('heroicon-o-photo')
            )
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
                PanelsRenderHook::BODY_START,
                function (): string {
                    // Inject our custom impersonation banner that works for all users
                    $banner = view('components.impersonate-banner')->render();

                    return $banner;
                }
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                function (): string {
                    if (! auth()->check()) {
                        return '';
                    }

                    $user = auth()->user();
                    $teamLink = $user->team ? route('public.team.index', $user->team->slug) : '#';
                    $teamName = $user->team ? $user->team->name : 'Department';

                    // Check if impersonating to adjust header positioning
                    $isImpersonating = Impersonation::isImpersonating();
                    $headerTop = $isImpersonating ? '50px' : '0';
                    $layoutMarginTop = $isImpersonating ? '110px' : '60px';
                    $sidebarMarginTop = $isImpersonating ? '70px' : '20px';

                    return '<style>
                        /* Hide original impersonate banner to use our custom one */
                        [id="impersonate-banner"]:not(.custom-banner) { display: none !important; }

                        .fi-topbar { display: none !important; }
                        .fi-layout { margin-top: '.$layoutMarginTop.' !important; }
                        .fi-main { padding-top: 0 !important; }
                        .fi-sidebar { margin-top: '.$sidebarMarginTop.' !important; }
                        .cmu-header {
                            position: fixed;
                            top: '.$headerTop.';
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

                        /* Ensure notifications appear above custom header */
                        .fi-notifications {
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

                        /* Curator Modal Fixes */
                        .fi-modal-window {
                            z-index: 2147483647 !important;
                        }

                        .fi-modal-backdrop {
                            z-index: 2147483646 !important;
                        }

                        /* Ensure Curator picker modal is properly styled */
                        [data-curator-picker-state] .fi-modal {
                            position: fixed !important;
                            z-index: 2147483647 !important;
                        }

                        /* Curator specific styling fixes */
                        .curator-picker-modal .fi-modal-content {
                            max-height: 90vh !important;
                            overflow-y: auto !important;
                        }

                        /* Fix Curator grid layout */
                        .curator-picker-modal .grid {
                            display: grid !important;
                            gap: 1rem !important;
                        }

                        /* Ensure Curator buttons and controls are visible */
                        .curator-picker-modal button,
                        .curator-picker-modal .fi-btn {
                            z-index: auto !important;
                            position: relative !important;
                        }

                        /* Fix Curator dropzone styling */
                        .curator-picker-modal .fi-dropzone {
                            border: 2px dashed #d1d5db !important;
                            border-radius: 0.5rem !important;
                            padding: 2rem !important;
                            text-align: center !important;
                            background: #f9fafb !important;
                        }

                        /* Fix Curator sidebar and main content areas */
                        .curator-picker-modal .curator-sidebar {
                            background: white !important;
                            border-right: 1px solid #e5e7eb !important;
                        }

                        .curator-picker-modal .curator-main {
                            background: white !important;
                        }

                        /* Ensure proper spacing and layout */
                        .curator-picker-modal .fi-section-content-ctn {
                            padding: 1rem !important;
                        }

                        /* Complete Curator Panel Styling */
                        .curator-panel {
                            background: white !important;
                            font-family: system-ui, -apple-system, sans-serif !important;
                        }
                        .curator-panel-toolbar {
                            background: #f3f4f6 !important;
                            border-bottom: 1px solid #e5e7eb !important;
                            padding: 1rem !important;
                        }
                        .curator-panel-gallery {
                            background: white !important;
                            padding: 1rem !important;
                        }
                        .curator-picker-grid {
                            display: grid !important;
                            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)) !important;
                            gap: 1rem !important;
                            list-style: none !important;
                            margin: 0 !important;
                            padding: 0 !important;
                        }
                        .curator-picker-grid li {
                            list-style: none !important;
                        }
                        .curator-picker-grid button {
                            width: 100% !important;
                            height: 100% !important;
                            border: 2px solid transparent !important;
                            border-radius: 0.5rem !important;
                            transition: all 0.2s !important;
                            cursor: pointer !important;
                            background: #f3f4f6 !important;
                        }
                        .curator-picker-grid button:hover {
                            border-color: #3b82f6 !important;
                            transform: scale(1.02) !important;
                        }
                        .checkered {
                            background-image:
                                linear-gradient(45deg, #f0f0f0 25%, transparent 25%),
                                linear-gradient(-45deg, #f0f0f0 25%, transparent 25%),
                                linear-gradient(45deg, transparent 75%, #f0f0f0 75%),
                                linear-gradient(-45deg, transparent 75%, #f0f0f0 75%) !important;
                            background-size: 20px 20px !important;
                            background-position: 0 0, 0 10px, 10px -10px, -10px 0px !important;
                        }
                        .curator-panel-sidebar {
                            background: #f9fafb !important;
                            border-left: 1px solid #e5e7eb !important;
                            width: 300px !important;
                            max-width: 300px !important;
                            flex-shrink: 0 !important;
                        }
                        .curator-panel-sidebar h4 {
                            font-weight: 600 !important;
                            font-size: 1rem !important;
                            color: #111827 !important;
                            margin: 0 !important;
                            padding: 1rem 1rem 0 1rem !important;
                        }
                        /* Fix Filament buttons in Curator */
                        .curator-panel .fi-btn, .curator-panel button {
                            display: inline-flex !important;
                            align-items: center !important;
                            padding: 0.5rem 1rem !important;
                            border-radius: 0.375rem !important;
                            font-weight: 500 !important;
                            cursor: pointer !important;
                            border: none !important;
                            background: #3b82f6 !important;
                            color: white !important;
                        }
                        .curator-panel .fi-btn:hover, .curator-panel button:hover {
                            background: #2563eb !important;
                        }
                        .curator-panel .fi-btn[color="gray"] {
                            background: #6b7280 !important;
                        }
                        .curator-panel .fi-btn[color="gray"]:hover {
                            background: #4b5563 !important;
                        }
                        /* Fix input styling */
                        .curator-panel input {
                            border: 1px solid #d1d5db !important;
                            border-radius: 0.375rem !important;
                            padding: 0.5rem 0.75rem !important;
                            background: white !important;
                        }
                        /* Fix layout issues */
                        .curator-panel .flex.flex-col.lg\\:flex-row {
                            display: flex !important;
                            flex-direction: column !important;
                        }
                        @media (min-width: 1024px) {
                            .curator-panel .flex.flex-col.lg\\:flex-row {
                                flex-direction: row !important;
                            }
                        }
                        /* Fix modal backdrop issues */
                        .fi-modal .curator-panel {
                            pointer-events: auto !important;
                        }

                        /* Reset and fix Curator modal completely */
                        .fi-modal .curator-panel * {
                            box-sizing: border-box !important;
                        }

                        /* Fix search bar styling */
                        .curator-panel input[type="search"],
                        .curator-panel .fi-input {
                            border: 1px solid #d1d5db !important;
                            border-radius: 0.375rem !important;
                            padding: 0.5rem 0.75rem !important;
                            background: white !important;
                            color: #111827 !important;
                            font-size: 0.875rem !important;
                        }

                        .curator-panel .fi-input-wrapper {
                            border: none !important;
                            background: none !important;
                        }

                        /* Fix main layout structure */
                        .curator-panel .flex-1.relative.flex.flex-col.lg\\:flex-row {
                            display: flex !important;
                            flex-direction: row !important;
                            height: 100% !important;
                        }

                        /* Fix gallery area */
                        .curator-panel .curator-panel-gallery {
                            flex: 1 !important;
                            padding: 1.5rem !important;
                            background: white !important;
                            overflow-y: auto !important;
                        }

                        /* Fix sidebar */
                        .curator-panel .curator-panel-sidebar {
                            width: 280px !important;
                            min-width: 280px !important;
                            max-width: 280px !important;
                            background: #f8fafc !important;
                            border-left: 1px solid #e2e8f0 !important;
                            padding: 1rem !important;
                        }

                        /* Fix directory buttons to look like normal folders */
                        .curator-panel button[wire\\:click*="handleDirectoryChange"] {
                            width: 100% !important;
                            height: auto !important;
                            padding: 0.75rem !important;
                            margin: 0.25rem 0 !important;
                            background: white !important;
                            border: 1px solid #e2e8f0 !important;
                            border-radius: 0.5rem !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: flex-start !important;
                            text-align: left !important;
                            color: #374151 !important;
                            font-size: 0.875rem !important;
                            transition: all 0.2s !important;
                        }

                        .curator-panel button[wire\\:click*="handleDirectoryChange"]:hover {
                            background: #f1f5f9 !important;
                            border-color: #cbd5e1 !important;
                            transform: none !important;
                        }

                        .curator-panel button[wire\\:click*="handleDirectoryChange"] .grid {
                            display: flex !important;
                            align-items: center !important;
                            gap: 0.5rem !important;
                        }

                        .curator-panel button[wire\\:click*="handleDirectoryChange"] svg {
                            width: 1.25rem !important;
                            height: 1.25rem !important;
                            color: #64748b !important;
                        }

                        /* Fix file grid */
                        .curator-panel ul.curator-picker-grid {
                            display: grid !important;
                            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)) !important;
                            gap: 1rem !important;
                            list-style: none !important;
                            margin: 0 !important;
                            padding: 0 !important;
                        }

                        /* Fix individual file items */
                        .curator-panel ul.curator-picker-grid li {
                            position: relative !important;
                            aspect-ratio: 1 !important;
                            list-style: none !important;
                        }

                        .curator-panel ul.curator-picker-grid li button {
                            width: 100% !important;
                            height: 100% !important;
                            padding: 0 !important;
                            margin: 0 !important;
                            border: 2px solid transparent !important;
                            border-radius: 0.5rem !important;
                            overflow: hidden !important;
                            background: #f8fafc !important;
                            transition: all 0.2s ease !important;
                        }

                        .curator-panel ul.curator-picker-grid li button:hover {
                            border-color: #3b82f6 !important;
                            transform: scale(1.02) !important;
                            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
                        }

                        /* Fix image display */
                        .curator-panel ul.curator-picker-grid img {
                            width: 100% !important;
                            height: 100% !important;
                            object-fit: cover !important;
                            object-position: center !important;
                        }

                        /* Fix selected state */
                        .curator-panel ul.curator-picker-grid li button.ring-2.ring-primary-500 {
                            border-color: #3b82f6 !important;
                            background: rgba(59, 130, 246, 0.1) !important;
                        }

                        /* Fix toolbar */
                        .curator-panel .curator-panel-toolbar {
                            background: #f8fafc !important;
                            border-bottom: 1px solid #e2e8f0 !important;
                            padding: 1rem 1.5rem !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: space-between !important;
                        }

                        /* Fix breadcrumbs */
                        .curator-panel ul.text-sm.flex.items-center {
                            list-style: none !important;
                            margin: 0 0 1rem 0 !important;
                            padding: 0 !important;
                            font-size: 0.875rem !important;
                            color: #64748b !important;
                        }

                        /* Fix controls at bottom */
                        .curator-panel .curator-panel-controls {
                            position: fixed !important;
                            bottom: 1rem !important;
                            left: 50% !important;
                            transform: translateX(-50%) !important;
                            z-index: 1000 !important;
                        }

                        .curator-panel .curator-panel-controls > div {
                            background: #1f2937 !important;
                            border-radius: 0.75rem !important;
                            padding: 1rem !important;
                            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
                        }

                        /* Remove any conflicting styles */
                        .curator-panel .bg-gray-700,
                        .curator-panel .checkered {
                            background: #f8fafc !important;
                        }

                        /* Fix close button */
                        .curator-panel [x-on\\:click="close()"] {
                            background: #6b7280 !important;
                            color: white !important;
                            border-radius: 0.375rem !important;
                            padding: 0.5rem !important;
                            border: none !important;
                            width: 2.5rem !important;
                            height: 2.5rem !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                            position: relative !important;
                            z-index: 10 !important;
                        }

                        /* Fix toolbar layout to prevent overlapping */
                        .curator-panel .curator-panel-toolbar .flex.items-center.gap-4 {
                            display: flex !important;
                            align-items: center !important;
                            gap: 1rem !important;
                        }

                        /* Fix deselect button size */
                        .curator-panel .curator-panel-controls .fi-btn,
                        .curator-panel .curator-panel-controls button {
                            padding: 0.5rem 1rem !important;
                            font-size: 0.875rem !important;
                            height: auto !important;
                            min-height: 2.5rem !important;
                            display: inline-flex !important;
                            align-items: center !important;
                            white-space: nowrap !important;
                        }

                        /* Fix selection controls container */
                        .curator-panel .curator-panel-controls > div {
                            background: rgba(31, 41, 55, 0.95) !important;
                            border-radius: 0.75rem !important;
                            padding: 0.75rem 1rem !important;
                            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
                            display: flex !important;
                            align-items: center !important;
                            gap: 0.75rem !important;
                            backdrop-filter: blur(10px) !important;
                        }

                        /* Ensure toolbar has proper spacing */
                        .curator-panel .curator-panel-toolbar {
                            min-height: 4rem !important;
                            flex-shrink: 0 !important;
                        }

                        /* Fix search input container */
                        .curator-panel .curator-panel-toolbar label.shrink-0 {
                            max-width: 300px !important;
                            flex-shrink: 0 !important;
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


                            // Fix Curator modal interaction issues
                            function fixCuratorModal() {
                                // Prevent modal from closing on click inside curator panel
                                document.addEventListener("click", function(e) {
                                    const curatorPanel = e.target.closest(".curator-panel");
                                    if (curatorPanel) {
                                        // Only allow explicit close button to close modal
                                        const isCloseButton = e.target.closest("[x-on\\\\:click=\\"close()\\"]") ||
                                                              e.target.matches("[x-on\\\\:click=\\"close()\\"]");
                                        if (!isCloseButton) {
                                            e.stopPropagation();
                                        }
                                    }
                                });

                                // Fix modal z-index when Curator opens
                                const observer = new MutationObserver(function(mutations) {
                                    mutations.forEach(function(mutation) {
                                        mutation.addedNodes.forEach(function(node) {
                                            if (node.nodeType === 1 && node.classList && node.classList.contains("curator-panel")) {
                                                const modal = node.closest(".fi-modal");
                                                if (modal) {
                                                    modal.style.zIndex = "2147483647";
                                                    modal.style.position = "fixed";
                                                }
                                            }
                                        });
                                    });
                                });

                                observer.observe(document.body, {
                                    childList: true,
                                    subtree: true
                                });
                            }

                            // Initialize curator fixes
                            fixCuratorModal();
                        });
                    </script>';
                }
            );
    }
}
