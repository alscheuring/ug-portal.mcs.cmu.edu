<?php

namespace App\Filament\Student\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class CompleteProfile extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    protected string $view = 'filament.student.pages.complete-profile';

    protected static ?string $title = 'Complete Profile';

    protected static ?string $navigationLabel = 'Complete Profile';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = auth()->user();

        // Only show this page if the user's profile is incomplete
        return $user && ! $user->hasCompletedProfile();
    }

    public function mount(): void
    {
        // Redirect if profile is already complete
        if (auth()->user()?->hasCompletedProfile()) {
            $this->redirect(auth()->user()->getRedirectUrl());
        }
    }
}
