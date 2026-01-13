<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    use HasFactory;

    protected $fillable = [
        'program_id', 'academic_year', 'tuition_fee', 'development_fee',
        'exam_fee', 'library_fee', 'other_fees', 'total_fee', 'installments', 'is_active'
    ];

    protected $casts = [
        'tuition_fee' => 'decimal:2',
        'development_fee' => 'decimal:2',
        'exam_fee' => 'decimal:2',
        'library_fee' => 'decimal:2',
        'other_fees' => 'decimal:2',
        'total_fee' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function studentFees()
    {
        return $this->hasMany(StudentFee::class);
    }
}