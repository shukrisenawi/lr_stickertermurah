<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
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
        $user = Auth::user();

        $myTestimonials = Testimonial::query()
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($t) {
                $t->image_url = $t->image_path
                    ? Storage::disk('public')->url($t->image_path)
                    : null;

                return $t;
            });

        $allTestimonials = Testimonial::query()
            ->where('is_approved', true)
            ->latest()
            ->get()
            ->map(function ($t) {
                $t->image_url = $t->image_path
                    ? Storage::disk('public')->url($t->image_path)
                    : null;

                return $t;
            });

        return Inertia::render('Member/Testimonials/Index', [
            'myTestimonials' => $myTestimonials,
            'allTestimonials' => $allTestimonials,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'business' => ['nullable', 'string', 'max:255'],
            'text' => ['required', 'string', 'max:2000'],
            'stars' => ['required', 'integer', 'min:1', 'max:5'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $data = [
            'name' => $validated['name'],
            'business' => $validated['business'] ?? null,
            'text' => $validated['text'],
            'stars' => $validated['stars'],
            'is_approved' => false,
            'user_id' => Auth::id(),
        ];

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('testimonials', 'public');
        }

        Testimonial::query()->create($data);

        return redirect()->route('member.testimonials.index')->with('success', 'Testimoni anda berjaya dihantar! Ia akan dipaparkan selepas diluluskan oleh admin.');
    }
}
