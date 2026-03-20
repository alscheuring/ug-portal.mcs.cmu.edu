<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <img src="{{ asset('images/mcs-logo.jpg') }}" alt="Mellon College of Science" class="w-16 h-16 rounded-full object-cover">
        </div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
            Complete Your Profile
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Welcome to the CMU Undergraduate Portal! Please select your department and year to complete your profile.
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10">
            @if (session('status'))
                <div class="mb-4 p-3 bg-green-100 border border-green-300 rounded text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <form wire:submit="completeProfile" class="space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <div class="mt-1">
                        <input
                            wire:model="name"
                            id="name"
                            name="name"
                            type="text"
                            required
                            placeholder="Enter your full name"
                            class="appearance-none block w-full px-3 py-2 border border-gray-300 rounded-md placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        />
                    </div>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Department -->
                <div>
                    <label for="department" class="block text-sm font-medium text-gray-700">Department</label>
                    <div class="mt-1">
                        <select
                            wire:model.live="department"
                            id="department"
                            name="department"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                            <option value="">Select your department</option>
                            @foreach($departmentOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('department')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">
                        Select the department you are affiliated with at the Mellon College of Science.
                    </p>
                </div>

                <!-- Year in Program -->
                <div>
                    <label for="year_in_program" class="block text-sm font-medium text-gray-700">Year in Program</label>
                    <div class="mt-1">
                        <select
                            wire:model="year_in_program"
                            id="year_in_program"
                            name="year_in_program"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        >
                            <option value="">Select your year</option>
                            @foreach($yearOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @error('year_in_program')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-500">
                        Select your current academic standing or role at CMU.
                    </p>
                </div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                        wire:target="completeProfile"
                    >
                        <span wire:loading wire:target="completeProfile" class="mr-2">
                            <svg class="animate-spin -ml-1 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="completeProfile">Complete Profile & Continue</span>
                        <span wire:loading wire:target="completeProfile">Completing Profile...</span>
                    </button>
                </div>
            </form>

            <!-- User Info Display -->
            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="text-sm text-gray-600">
                    <p><strong>Andrew ID:</strong> {{ auth()->user()->andrew_id }}</p>
                    <p><strong>Email:</strong> {{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>
    </div>
</div>