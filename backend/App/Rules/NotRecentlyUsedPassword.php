<?php

namespace App\Rules;

use App\Models\User;
use App\Services\PasswordHistoryService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotRecentlyUsedPassword implements ValidationRule
{
    public function __construct(private ?User $user)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->user || !is_string($value) || $value === '') {
            return;
        }

        $service = app(PasswordHistoryService::class);
        $limit = max((int) config('security.auth.password_history_limit', 5), 1);

        if ($service->wasRecentlyUsed($this->user, $value, $limit)) {
            $fail("No puedes reutilizar ninguna de tus ultimas {$limit} contrasenas.");
        }
    }
}
