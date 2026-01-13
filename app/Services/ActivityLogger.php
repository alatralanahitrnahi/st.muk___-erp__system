<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log(string $action, $model = null, array $oldValues = [], array $newValues = [])
    {
        $request = request();
        
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
    }

    public static function created($model, array $attributes = [])
    {
        self::log('created', $model, [], $attributes ?: $model->toArray());
    }

    public static function updated($model, array $oldValues = [], array $newValues = [])
    {
        self::log('updated', $model, $oldValues, $newValues);
    }

    public static function deleted($model)
    {
        self::log('deleted', $model, $model->toArray(), []);
    }

    public static function login()
    {
        self::log('login');
    }

    public static function logout()
    {
        self::log('logout');
    }
}