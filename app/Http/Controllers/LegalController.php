<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    public function privacyPolicy(): Response
    {
        return Inertia::render('Public/PrivacyPolicy');
    }

    public function termsOfService(): Response
    {
        return Inertia::render('Public/TermsOfService');
    }
}
