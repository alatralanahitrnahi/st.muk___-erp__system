<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacultyAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'subject_component_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subjectComponent()
    {
        return $this->belongsTo(SubjectComponent::class);
    }
}
