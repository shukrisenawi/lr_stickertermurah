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
                'preview_files' => collect($this->previewPathsFor($project))
                    ->values()
                    ->map(fn (string $path, int $index) => [
                        'url' => route('member.projects.preview', ['project' => $project, 'preview' => $index]),
                    ])
                    ->all(),
                'order_id' => $project->order_id,
                'order_no' => $project->order?->order_no,
                'created_at' => $project->created_at,
            ])
            ->filter(fn (array $project) => count($project['preview_files']) > 0)
            ->values();

        return Inertia::render('Member/Projects/Index', ['projects' => $projects]);
    }

    public function preview(CustomerProject $project, ?int $preview = null)
    {
        abort_if($project->user_id !== Auth::id(), 403);
        $previewPaths = $this->previewPathsFor($project);
        $previewPath = $previewPaths[$preview ?? 0] ?? null;
        abort_unless($previewPath && Storage::exists($previewPath), 404);

        return response()->file(Storage::path($previewPath), [
            'Cache-Control' => 'private, max-age=604800',
        ]);
    }

    /** @return list<string> */
    private function previewPathsFor(CustomerProject $project): array
    {
        return collect($project->preview_paths ?: ($project->preview_path ? [$project->preview_path] : []))
            ->filter(fn ($path): bool => is_string($path) && in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true))
            ->values()
            ->all();
    }
}
