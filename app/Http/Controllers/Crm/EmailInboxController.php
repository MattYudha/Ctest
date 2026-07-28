<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EmailInboxController extends Controller
{
    public function index()
    {
        // $blasts = $this->getBlastQuery()->latest()->paginate(10);
        return view('crm.email_inbox.index');
    }
}
