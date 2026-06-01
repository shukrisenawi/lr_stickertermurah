<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnderConstructionController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/Settings/UnderConstruction', [
            'isEnabled' => Setting::getValue('under_construction', '0') === '1',
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'is_enabled' => ['required', 'boolean'],
        ]);

        Setting::setValue('under_construction', $validated['is_enabled'] ? '1' : '0');

        return redirect()->route('admin.settings.under-construction.edit')
            ->with('success', 'Mod Under Construction berjaya dikemaskini.');
    }
}
