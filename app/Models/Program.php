<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'level',
        'department_id',
        'duration_years',
        'total_semesters',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_years' => 'integer',
        'total_semesters' => 'integer',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}