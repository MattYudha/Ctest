<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmWaBlast extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_name',
        'body_template',
        'status',
        'target_count',
        'sent_count',
        'created_by'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recipients()
    {
        return $this->hasMany(CrmWaBlastRecipient::class, 'wa_blast_id');
    }
}
