<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

trait HasVisibilityScope
{
    public function scopeVisibleTo(Builder $query, $user)
    {
        if ($user->user_type === 'super-admin') {
            return $query;
        }

        $role = $user->user_type;
        $modelName = strtolower(class_basename($this));

        $rules = DB::table('visibility_rules')
            ->where('module_name', $modelName . 's')
            ->where('role_name', $role)
            ->where('is_active', true)
            ->get();

        foreach ($rules as $rule) {
            $config = json_decode($rule->rule_config, true);
            
            if ($rule->rule_type === 'scope') {
                $this->applyScope($query, $config['scope'], $user);
            }
        }

        return $query;
    }

    private function applyScope(Builder $query, $scope, $user)
    {
        switch ($scope) {
            case 'own_profile':
                $query->where('user_id', $user->id);
                break;
                
            case 'assigned_classes':
                $query->whereHas('enrollments', function($q) use ($user) {
                    $q->whereHas('subject', function($sq) use ($user) {
                        $sq->where('faculty_id', $user->id);
                    });
                });
                break;
        }
    }
}
