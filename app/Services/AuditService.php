<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public function log(string $action, string $model, int $modelId, array $oldValues = [], array $newValues = [])
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model' => $model,
            'model_id' => $modelId,
            'old_values' => json_encode($oldValues),
            'new_values' => json_encode($newValues),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function getLogs($model = null, $userId = null)
    {
        $query = AuditLog::query();

        if ($model) {
            $query->where('model', $model);
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}