<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class CrmContact extends Model
{
    use HasFactory, Auditable;

    protected $fillable = ['company_name', 'address', 'phone', 'has_website', 'website_url', 'email', 'source', 'created_by'];

    protected $casts = [
        'has_website' => 'boolean',
    ];
}
