<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class NoticeController extends Controller
{
    /**
     * Display notices list.
     */
    public function index(): Response
    {
        $notices = Notice::orderBy('publish_date', 'desc')->latest()->get();

        return Inertia::render('notices/Index', [
            'notices' => $notices,
        ]);
    }

    /**
     * Show the form for creating a new notice.
     */
    public function create(): Response
    {
        return Inertia::render('notices/CreateEdit', [
            'notice' => null,
        ]);
    }

    /**
     * Store a newly created notice.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|in:exam,holiday,event,general,admission,urgent',
            'publish_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:publish_date',
            'target_audience' => 'required|string|in:all,students,teachers,parents',
            'status' => 'required|string|in:active,inactive,draft',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ]);

        $validated['posted_by'] = 'Admin';

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('notices', 'public');
            $validated['attachment_path'] = $path;
        }

        if ($request->hasFile('featured_image')) {
            $imagePath = $request->file('featured_image')->store('notices/images', 'public');
            $validated['image_path'] = $imagePath;
        }

        Notice::create($validated);

        return redirect()->route('notices.index')->with('success', 'Notice published successfully.');
    }

    /**
     * Show the form for editing the notice.
     */
    public function edit(Notice $notice): Response
    {
        return Inertia::render('notices/CreateEdit', [
            'notice' => $notice,
        ]);
    }

    /**
     * Update the notice.
     */
    public function update(Request $request, Notice $notice): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|string|in:exam,holiday,event,general,admission,urgent',
            'publish_date' => 'required|date',
            'expiry_date' => 'nullable|date|after_or_equal:publish_date',
            'target_audience' => 'required|string|in:all,students,teachers,parents',
            'status' => 'required|string|in:active,inactive,draft',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
            'remove_image' => 'nullable|boolean',
        ]);

        if ($request->hasFile('attachment')) {
            if ($notice->attachment_path !== null) {
                Storage::disk('public')->delete($notice->attachment_path);
            }
            $path = $request->file('attachment')->store('notices', 'public');
            $validated['attachment_path'] = $path;
        }

        if (! empty($validated['remove_image']) && $notice->image_path !== null) {
            Storage::disk('public')->delete($notice->image_path);
            $validated['image_path'] = null;
        }

        if ($request->hasFile('featured_image')) {
            if ($notice->image_path !== null) {
                Storage::disk('public')->delete($notice->image_path);
            }
            $imagePath = $request->file('featured_image')->store('notices/images', 'public');
            $validated['image_path'] = $imagePath;
        }

        $notice->update($validated);

        return redirect()->route('notices.index')->with('success', 'Notice updated successfully.');
    }

    /**
     * Remove the notice.
     */
    public function destroy(Notice $notice): RedirectResponse
    {
        if ($notice->attachment_path !== null) {
            Storage::disk('public')->delete($notice->attachment_path);
        }
        $notice->delete();

        return redirect()->route('notices.index')->with('success', 'Notice deleted successfully.');
    }
}
