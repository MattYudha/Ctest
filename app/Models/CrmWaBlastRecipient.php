<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmWaBlastRecipient extends Model
{
    use HasFactory;

    protected $fillable = [
        'wa_blast_id',
        'contact_id',
        'phone_number',
        'status'
    ];

    public function blast()
    {
        return $this->belongsTo(CrmWaBlast::class, 'wa_blast_id');
    }

    public function contact()
    {
        return $this->belongsTo(CrmContact::class, 'contact_id');
    }
}
