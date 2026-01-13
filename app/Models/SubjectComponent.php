<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubjectComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject_id',
        'component_type',
        'max_marks',
        'pass_marks',
        'attendance_required'
    ];

    protected $casts = [
        'max_marks' => 'decimal:2',
        'pass_marks' => 'decimal:2',
        'attendance_required' => 'boolean',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function facultyAssignments()
    {
        return $this->hasMany(FacultyAssignment::class);
    }
}
