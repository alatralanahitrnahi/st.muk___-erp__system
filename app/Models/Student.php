<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasVisibilityScope;
use App\Traits\HasWorkflow;

class Student extends Model
{
    use HasFactory, HasVisibilityScope, HasWorkflow;

    protected $fillable = [
        'user_id',
        'program_id',
        'category_id',
        'department_id',
        'admission_number',
        'prn_number',
        'status',
        'scholarship_applied',
        'admission_date',
        'documents',
        'application_status',
        'application_date',
        'application_fee_paid',
        'parent_name',
        'parent_phone',
        'parent_email',
        'previous_school',
        'previous_percentage',
        'approved_by',
        'approved_at',
        'rejection_reason'
    ];

    protected $casts = [
        'scholarship_applied' => 'boolean',
        'admission_date' => 'date',
        'documents' => 'array',
        'application_fee_paid' => 'boolean',
        'application_date' => 'date',
        'previous_percentage' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
