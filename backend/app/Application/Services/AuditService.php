<?php

namespace App\Application\Services;

use App\Models\Audit;
use Illuminate\Contracts\Auth\Authenticatable;

class AuditService
{
    public function record(?Authenticatable $actor, string $action, string $entityType, int|string $entityId, array $meta = []): void
    {
        Audit::create([
            'actor_user_id' => $actor?->getAuthIdentifier(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => (int) $entityId,
            'meta' => $meta ?: null,
        ]);
    }
}

