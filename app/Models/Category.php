<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'fee_percentage',
        'is_active'
    ];

    protected $casts = [
        'fee_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}