<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SiteSettingController extends Controller
{
    /**
     * Show the edit site settings page.
     */
    public function edit(): Response
    {
        $settings = SiteSetting::first() ?? SiteSetting::create([
            'institute_name' => 'Saraswati Vidyaniketan',
            'tagline' => 'EST. 1986 · DHAKA',
            'logo_path' => null,
            'favicon_path' => null,
        ]);

        return Inertia::render('settings/SiteSettings', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update the site settings.
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
            'institute_name' => ['required', 'string', 'max:255'],
            'tagline' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,ico,webp', 'max:512'],
            'remove_logo' => ['boolean'],
            'remove_favicon' => ['boolean'],
        ]);

        $data = [
            'institute_name' => $validated['institute_name'],
            'tagline' => $validated['tagline'],
        ];

        // Handle logo
        if (! empty($validated['remove_logo']) && $settings->logo_path) {
            Storage::disk('public')->delete($settings->logo_path);
            $data['logo_path'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($settings->logo_path) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('site', 'public');
        }

        // Handle favicon
        if (! empty($validated['remove_favicon']) && $settings->favicon_path) {
            Storage::disk('public')->delete($settings->favicon_path);
            $data['favicon_path'] = null;
        }

        if ($request->hasFile('favicon')) {
            if ($settings->favicon_path) {
                Storage::disk('public')->delete($settings->favicon_path);
            }
            $data['favicon_path'] = $request->file('favicon')->store('site', 'public');
        }

        $settings->update($data);

        return redirect()->back()->with('success', 'Site settings updated successfully.');
    }
}
