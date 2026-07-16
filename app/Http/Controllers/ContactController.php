<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    /**
     * Show the edit Contact Us page.
     */
    public function edit(): Response
    {
        $settings = SiteSetting::first() ?? SiteSetting::create([
            'institute_name' => 'Saraswati Vidyaniketan',
            'tagline' => 'EST. 1986 · DHAKA',
            'logo_path' => null,
            'favicon_path' => null,
            'contact_address' => '124, Mirpur Road, Dhaka — 1207',
            'contact_phone' => '02-9876543',
            'contact_email' => 'info@saraswatividya.edu.bd',
            'contact_hours' => "Sat — Thu : 8:00 AM — 4:00 PM\nFriday : Closed",
        ]);

        return Inertia::render('contact/Edit', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update the Contact details.
     */
    public function update(Request $request): RedirectResponse
    {
        $settings = SiteSetting::first() ?? SiteSetting::create([
            'institute_name' => 'Saraswati Vidyaniketan',
            'tagline' => 'EST. 1986 · DHAKA',
            'logo_path' => null,
            'favicon_path' => null,
        ]);

        $validated = $request->validate([
            'contact_address' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:100'],
            'contact_email' => ['required', 'email', 'max:255'],
            'contact_hours' => ['required', 'string'],
        ]);

        $settings->update($validated);

        return redirect()->back()->with('success', 'Contact details updated successfully.');
    }
}
