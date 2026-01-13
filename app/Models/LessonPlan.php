<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'faculty_id',
        'subject_id',
        'program_id',
        'semester_id',
        'academic_year_id',
        'lesson_date',
        'lesson_number',
        'topic',
        'objectives',
        'materials_required',
        'teaching_method',
        'lesson_procedure',
        'assessment_method',
        'differentiation_notes',
        'reflection_notes',
        'status',
        'planned_duration',
        'actual_duration',
        'attendance_count',
        'hod_approved_at',
        'hod_approved_by',
        'principal_approved_at',
        'principal_approved_by'
    ];

    protected $casts = [
        'lesson_date' => 'date',
        'objectives' => 'array',
        'materials_required' => 'array',
        'hod_approved_at' => 'datetime',
        'principal_approved_at' => 'datetime'
    ];

    // Relationships
    public function faculty()
    {
        return $this->belongsTo(User::class, 'faculty_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function hodApprover()
    {
        return $this->belongsTo(User::class, 'hod_approved_by');
    }

    public function principalApprover()
    {
        return $this->belongsTo(User::class, 'principal_approved_by');
    }

    // Scopes
    public function scopeForFaculty($query, $facultyId)
    {
        return $query->where('faculty_id', $facultyId);
    }

    public function scopeForSubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}