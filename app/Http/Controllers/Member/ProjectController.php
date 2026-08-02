<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\CustomerProject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(): Response
    {
        $projects = CustomerProject::query()
            ->where('user_id', Auth::id())
            ->with('order')
            ->latest()
            ->get()
            ->map(fn (CustomerProject $project) => [
                'id' => $project->id,
                'title' => $project->title,
                'notes' => $project->notes,
                'preview_url' => route('member.projects.preview', $project),
                'order_id' => $project->order_id,
                'order_no' => $project->order?->order_no,
                'created_at' => $project->created_at,
            ]);

        return Inertia::render('Member/Projects/Index', ['projects' => $projects]);
    }

    public function preview(CustomerProject $project)
    {
        abort_if($project->user_id !== Auth::id(), 403);
        abort_unless(Storage::exists($project->preview_path), 404);

        return response()->file(Storage::path($project->preview_path));
    }
}
