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
        $notices = Notice::orderBy('publish_date', 'desc')->get();
        
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
        ]);

        $validated['posted_by'] = auth()->user()->name ?? 'Principal Office';

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('notices', 'public');
            $validated['attachment_path'] = $path;
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
        ]);

        if ($request->hasFile('attachment')) {
            if ($notice->attachment_path !== null) {
                Storage::disk('public')->delete($notice->attachment_path);
            }
            $path = $request->file('attachment')->store('notices', 'public');
            $validated['attachment_path'] = $path;
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
