<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_current'];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }
}
