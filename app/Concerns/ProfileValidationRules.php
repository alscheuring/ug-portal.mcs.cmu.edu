<?php

namespace App\Concerns;

use App\Models\User;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    /**
     * Get the validation rules used to validate user profiles.
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>>
     */
    protected function profileRules(?int $userId = null): array
    {
        return [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($userId),
            'department' => $this->departmentRules(),
            'year_in_program' => $this->yearInProgramRules(),
            'major' => $this->majorRules(),
        ];
    }

    /**
     * Get the validation rules for profile completion (required fields only).
     *
     * @return array<string, array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>>
     */
    protected function profileCompletionRules(): array
    {
        return [
            'name' => $this->nameRules(),
            'department' => $this->departmentRules(),
            'year_in_program' => $this->yearInProgramRules(),
        ];
    }

    /**
     * Get the validation rules used to validate user names.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function nameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * Get the validation rules used to validate user emails.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function emailRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique(User::class)
                : Rule::unique(User::class)->ignore($userId),
        ];
    }

    /**
     * Get the validation rules used to validate department.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function departmentRules(): array
    {
        return [
            'required',
            'string',
            Rule::in([
                'Biological Sciences',
                'Mathematical Sciences',
                'Chemistry',
                'Physics',
                'Mellon College of Science', // For staff/admin
            ]),
        ];
    }

    /**
     * Get the validation rules used to validate year in program.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function yearInProgramRules(): array
    {
        return [
            'required',
            'string',
            Rule::in([
                'Freshman',
                'Sophomore',
                'Junior',
                'Senior',
            ]),
        ];
    }

    /**
     * Get the validation rules used to validate major.
     *
     * @return array<int, \Illuminate\Contracts\Validation\Rule|array<mixed>|string>
     */
    protected function majorRules(): array
    {
        return [
            'required',
            'string',
            'max:255',
        ];
    }
}
