<?php

namespace App\Services;

use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class PasswordHistoryService
{
    public function wasRecentlyUsed(User $user, string $plainPassword, ?int $limit = null): bool
    {
        if ($plainPassword === '') {
            return false;
        }

        $historyLimit = $this->resolveLimit($limit);

        if (!empty($user->password) && Hash::check($plainPassword, (string) $user->password)) {
            return true;
        }

        if (!Schema::hasTable('password_histories')) {
            return false;
        }

        $hashes = PasswordHistory::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit($historyLimit)
            ->pluck('password');

        foreach ($hashes as $hash) {
            if (Hash::check($plainPassword, (string) $hash)) {
                return true;
            }
        }

        return false;
    }

    public function record(User $user, ?string $hashedPassword = null): void
    {
        if (!Schema::hasTable('password_histories')) {
            return;
        }

        $hash = (string) ($hashedPassword ?? $user->password ?? '');
        if ($hash === '') {
            return;
        }

        PasswordHistory::create([
            'user_id' => $user->id,
            'password' => $hash,
        ]);

        $this->prune($user);
    }

    private function prune(User $user): void
    {
        $historyLimit = $this->resolveLimit(null);

        $idsToKeep = PasswordHistory::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->limit($historyLimit)
            ->pluck('id');

        if ($idsToKeep->isEmpty()) {
            return;
        }

        PasswordHistory::query()
            ->where('user_id', $user->id)
            ->whereNotIn('id', $idsToKeep)
            ->delete();
    }

    private function resolveLimit(?int $limit): int
    {
        if ($limit !== null) {
            return max($limit, 1);
        }

        return max((int) config('security.auth.password_history_limit', 5), 1);
    }
}
