<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Support\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class TestimonialController extends Controller
{
    public function index(): Response
    {
        $testimonials = Testimonial::query()
            ->with(['approvedBy', 'user'])
            ->latest()
            ->paginate(20);

        $testimonials->getCollection()->transform(function ($t) {
            $t->image_url = $t->image_path
                ? Storage::disk('public')->url($t->image_path)
                : null;

            return $t;
        });

        return Inertia::render('Admin/Testimonials/Index', [
            'testimonials' => $testimonials,
        ]);
    }

    public function approve(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update([
            'is_approved' => true,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimoni berjaya diluluskan.');
    }

    public function reject(Testimonial $testimonial): RedirectResponse
    {
        $testimonial->update([
            'is_approved' => false,
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimoni telah ditolak.');
    }

    public function edit(Testimonial $testimonial): Response
    {
        $testimonial->image_url = $testimonial->image_path
            ? Storage::disk('public')->url($testimonial->image_path)
            : null;

        return Inertia::render('Admin/Testimonials/Edit', [
            'testimonial' => $testimonial,
        ]);
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business' => ['nullable', 'string', 'max:255'],
            'text' => ['required', 'string', 'max:2000'],
            'stars' => ['required', 'integer', 'min:1', 'max:5'],
            'is_approved' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $data = [
            'name' => $validated['name'],
            'business' => $validated['business'] ?? null,
            'text' => $validated['text'],
            'stars' => $validated['stars'],
            'is_approved' => $request->boolean('is_approved'),
        ];

        if ($request->hasFile('image')) {
            if ($testimonial->image_path) {
                Storage::disk('public')->delete($testimonial->image_path);
            }
            $data['image_path'] = ImageOptimizer::store($request->file('image'), 'testimonials', 1200, 900, 80);
        }

        $testimonial->update($data);

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimoni berjaya dikemaskini.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        if ($testimonial->image_path) {
            Storage::disk('public')->delete($testimonial->image_path);
        }
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')->with('success', 'Testimoni berjaya dipadam.');
    }
}
