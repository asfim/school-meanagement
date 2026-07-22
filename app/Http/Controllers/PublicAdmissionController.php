<?php

namespace App\Http\Controllers;

use App\Models\AdmissionForm;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class PublicAdmissionController extends Controller
{
    public function create()
    {
        return Inertia::render('PublicAdmission', [
            'siteSettings' => SiteSetting::first(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'program' => 'required|string|max:255',
            'semester' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'mother_name' => 'nullable|string|max:255',
            'parent_contact' => 'nullable|string|max:20',
            'image' => 'nullable|image|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('admissions', 'public');
        }

        AdmissionForm::create([
            'name' => $validated['name'],
            'image_path' => $imagePath,
            'phone_number' => $validated['phone_number'],
            'program' => $validated['program'],
            'semester' => $validated['semester'],
            'father_name' => $validated['father_name'],
            'mother_name' => $validated['mother_name'],
            'parent_contact' => $validated['parent_contact'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Admission form submitted successfully.');
    }
}
