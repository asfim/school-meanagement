<?php

namespace App\Http\Controllers;

use App\Models\CampusLifeItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CampusLifeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('campus-life/Index', [
            'items' => CampusLifeItem::orderBy('sort_order')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('campus-life/CreateEdit', [
            'item' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('campus-life', 'public');
        }

        unset($validated['image']);
        CampusLifeItem::create($validated);

        return redirect()->route('campus-life.index')
            ->with('success', 'Gallery item created successfully.');
    }

    public function edit(CampusLifeItem $item): Response
    {
        return Inertia::render('campus-life/CreateEdit', [
            'item' => $item,
        ]);
    }

    public function update(Request $request, CampusLifeItem $item): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'remove_image' => ['boolean'],
        ]);

        if (! empty($validated['remove_image']) && $item->image_path) {
            Storage::disk('public')->delete($item->image_path);
            $validated['image_path'] = null;
        }

        if ($request->hasFile('image')) {
            if ($item->image_path) {
                Storage::disk('public')->delete($item->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('campus-life', 'public');
        }

        unset($validated['image'], $validated['remove_image']);
        $item->update($validated);

        return redirect()->route('campus-life.index')
            ->with('success', 'Gallery item updated successfully.');
    }

    public function destroy(CampusLifeItem $item): RedirectResponse
    {
        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return redirect()->route('campus-life.index')
            ->with('success', 'Gallery item deleted.');
    }
}
