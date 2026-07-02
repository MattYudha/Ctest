<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmDeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'crm_contact_id',
        'value',
        'status',
        'order',
        'created_by',
    ];

    public function contact()
    {
        return $this->belongsTo(CrmContact::class, 'crm_contact_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
