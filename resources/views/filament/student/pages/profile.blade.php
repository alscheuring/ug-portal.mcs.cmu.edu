<x-filament-panels::page>
    @if (!auth()->user()->hasCompletedProfile())
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
            <div class="flex">
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        Complete Your Profile
                    </h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>Please complete all required fields to access your department portal and course materials.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="space-y-6">
        <form wire:submit="save">
            {{ $this->form }}

            <div class="flex justify-end mt-6">
                {{ $this->getSaveFormAction() }}
            </div>
        </form>
    </div>

    @if (auth()->user()->hasCompletedProfile())
        <div class="mt-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex">
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">
                        Profile Complete ✓
                    </h3>
                    <div class="mt-2 text-sm text-green-700">
                        <p>
                            Your profile is complete! You now have access to the
                            @if(auth()->user()->team)
                                <a href="/{{ auth()->user()->team->slug }}" class="font-semibold underline">
                                    {{ auth()->user()->team->name }} portal
                                </a>
                            @else
                                department portal
                            @endif
                            and all course materials.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>