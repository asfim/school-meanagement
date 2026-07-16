<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AboutUsController extends Controller
{
    /**
     * Show the edit About Us page.
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

        return Inertia::render('about/Edit', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update the About Us page details.
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
            'about_title' => ['required', 'string', 'max:255'],
            'about_description' => ['required', 'string'],
            'about_stats' => ['nullable', 'array'],
            'about_stats.*.label' => ['required', 'string', 'max:100'],
            'about_stats.*.value' => ['required', 'string', 'max:50'],
        ]);

        $settings->update([
            'about_title' => $validated['about_title'],
            'about_description' => $validated['about_description'],
            'about_stats' => $validated['about_stats'] ?? null,
        ]);

        return redirect()->back()->with('success', 'About Us content updated successfully.');
    }
}
