<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class GoogleContactController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Contacts/Google', [
            'contacts' => [],
            'isConnected' => false,
            'isConfigured' => false,
            'error' => 'Ciri Google Contacts telah dihentikan. Sila gunakan fungsi lain.',
        ]);
    }
}
