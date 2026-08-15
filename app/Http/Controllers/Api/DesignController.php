<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StickerDesign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DesignController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $offset = max(0, (int) $request->input('offset', 0));
        $limit = max(1, min(60, (int) $request->input('limit', 12)));
        $category = $request->input('category');
        $tag = $request->input('tag');
        $search = trim((string) $request->input('search', ''));

        $query = StickerDesign::query()
            ->where('is_active', true)
            ->with('category')
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($category && $category !== 'Semua') {
            $query->whereHas('category', fn ($q) => $q->where('name', $category));
        }

        if ($tag) {
            $query->whereJsonContains('tags', $tag);
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhereJsonContains('tags', $search);
            });
        }

        $total = $query->count();

        $designs = $query
            ->skip($offset)
            ->take($limit)
            ->get()
            ->map(function ($design) {
                $imageUrl = $design->image_path
                    ? Storage::disk('public')->url($design->image_path)
                    : null;

                return [
                    'id' => $design->id,
                    'name' => $design->name,
                    'category' => $design->category?->name ?? 'Lain-lain',
                    'tags' => $design->tags ?? [],
                    'image' => $imageUrl,
                    'mobile_image' => $design->mobile_image_path
                        ? Storage::disk('public')->url($design->mobile_image_path)
                        : $imageUrl,
                ];
            });

        return response()->json([
            'data' => $designs,
            'meta' => [
                'offset' => $offset,
                'limit' => $limit,
                'total' => $total,
                'has_more' => $offset + $designs->count() < $total,
            ],
        ]);
    }
}
