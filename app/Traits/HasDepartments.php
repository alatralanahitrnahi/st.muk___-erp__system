<?php

namespace App\Traits;

use App\Models\Department;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasDepartments
{
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'user_departments')
                    ->withPivot('role_in_department', 'is_primary', 'start_date', 'end_date')
                    ->withTimestamps();
    }

    public function primaryDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'primary_department_id');
    }

    public function hasAccessToDepartment($departmentId): bool
    {
        if ($this->user_type === 'super-admin') {
            return true;
        }

        if ($this->primary_department_id == $departmentId) {
            return true;
        }

        return $this->departments()->where('department_id', $departmentId)->exists();
    }

    public function accessibleDepartmentIds(): array
    {
        if ($this->user_type === 'super-admin') {
            return Department::pluck('id')->toArray();
        }

        $departmentIds = $this->departments()->pluck('department_id')->toArray();
        
        if ($this->primary_department_id) {
            $departmentIds[] = $this->primary_department_id;
        }

        return array_unique($departmentIds);
    }

    public function getRoleInDepartment($departmentId): ?string
    {
        $userDept = $this->departments()
                         ->where('department_id', $departmentId)
                         ->first();
        
        return $userDept ? $userDept->pivot->role_in_department : null;
    }
}
