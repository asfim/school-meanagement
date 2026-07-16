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
            'about_title' => '40 Years of Education, Discipline & Values',
            'about_description' => 'Since 1986, we have been shaping the future of thousands of students. We believe true education means nurturing the hidden potential within every child with the right care and guidance.',
            'about_stats' => [
                ['label' => 'Students', 'value' => '1850+'],
                ['label' => 'Teachers', 'value' => '96'],
                ['label' => 'Pass Rate', 'value' => '98%'],
                ['label' => 'Years of Experience', 'value' => '40+'],
            ],
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
            'about_title' => ['nullable', 'string', 'max:255'],
            'about_description' => ['nullable', 'string'],
            'about_stats' => ['nullable', 'array'],
            'about_stats.*.label' => ['required', 'string', 'max:100'],
            'about_stats.*.value' => ['required', 'string', 'max:50'],
        ]);

        $data = [
            'institute_name' => $validated['institute_name'],
            'tagline' => $validated['tagline'],
            'about_title' => $validated['about_title'] ?? null,
            'about_description' => $validated['about_description'] ?? null,
            'about_stats' => $validated['about_stats'] ?? null,
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
