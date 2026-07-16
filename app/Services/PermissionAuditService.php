<?php

namespace App\Services;

use App\Models\PermissionAudit;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class PermissionAuditService
{
    public function recordSync(User $target, array $beforeIds, array $afterIds, ?User $actor, string $context): void
    {
        if (! Schema::hasTable('permission_audits')) {
            return;
        }

        $beforeIds = collect($beforeIds)->map(fn ($id) => (int) $id)->unique();
        $afterIds = collect($afterIds)->map(fn ($id) => (int) $id)->unique();

        $records = [];

        foreach ($afterIds->diff($beforeIds) as $permissionId) {
            $records[] = $this->auditRow($target, $actor, $permissionId, 'granted', $context);
        }

        foreach ($beforeIds->diff($afterIds) as $permissionId) {
            $records[] = $this->auditRow($target, $actor, $permissionId, 'revoked', $context);
        }

        if ($records !== []) {
            PermissionAudit::query()->insert($records);
        }
    }

    private function auditRow(User $target, ?User $actor, int $permissionId, string $action, string $context): array
    {
        return [
            'actor_user_id' => $actor?->user_id,
            'target_user_id' => $target->user_id,
            'permission_id' => $permissionId,
            'action' => $action,
            'context' => $context,
            'created_at' => now(),
        ];
    }
}
