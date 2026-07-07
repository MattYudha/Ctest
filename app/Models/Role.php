<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'access',
    ];

    protected $casts = [
        'access' => 'array',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get the KPIs assigned to this role
     */
    public function kpis()
    {
        return $this->belongsToMany(KPI::class, 'role_kpi', 'role_id', 'kpi_id')
            ->withPivot(['target_value', 'weight'])
            ->withTimestamps();
    }
}
