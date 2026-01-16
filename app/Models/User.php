<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\HasDepartments;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasDepartments;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'user_type',
        'is_active',
        'role',
        'designation',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function hasPermission($permission)
    {
        // Simple permission check based on role
        $rolePermissions = [
            'super-admin' => ['*'],
            'principal' => ['view_all', 'approve_all', 'manage_all'],
            'registrar' => ['view_department', 'manage_department', 'approve_department'],
            'faculty' => ['view_students', 'mark_attendance', 'enter_results'],
            'student' => ['view_own', 'view_results', 'pay_fees'],
        ];
        
        $permissions = $rolePermissions[$this->role] ?? [];
        return in_array('*', $permissions) || in_array($permission, $permissions);
    }
}
