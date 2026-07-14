<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BannerController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('banners/Index', [
            'banners' => Banner::orderBy('sort_order')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('banners/CreateEdit', [
            'banner' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'paragraph' => ['nullable', 'string'],
            'bg_color' => ['required', 'in:forest,ink,brass'],
            'overlay_color' => ['required', 'in:none,dark,dark-heavy,forest,ink,brass'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('banners', 'public');
        }

        unset($validated['image']);
        Banner::create($validated);

        return redirect()->route('banners.index')
            ->with('success', 'Banner created successfully.');
    }

    public function edit(Banner $banner): Response
    {
        return Inertia::render('banners/CreateEdit', [
            'banner' => $banner,
        ]);
    }

    public function update(Request $request, Banner $banner): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'paragraph' => ['nullable', 'string'],
            'bg_color' => ['required', 'in:forest,ink,brass'],
            'overlay_color' => ['required', 'in:none,dark,dark-heavy,forest,ink,brass'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'remove_image' => ['boolean'],
        ]);

        if (! empty($validated['remove_image']) && $banner->image_path) {
            Storage::disk('public')->delete($banner->image_path);
            $validated['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            if ($banner->image_path) {
                Storage::disk('public')->delete($banner->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('banners', 'public');
        }

        unset($validated['image'], $validated['remove_image']);
        $banner->update($validated);

        return redirect()->route('banners.index')
            ->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        if ($banner->image_path) {
            Storage::disk('public')->delete($banner->image_path);
        }

        $banner->delete();

        return redirect()->route('banners.index')
            ->with('success', 'Banner deleted.');
    }
}
