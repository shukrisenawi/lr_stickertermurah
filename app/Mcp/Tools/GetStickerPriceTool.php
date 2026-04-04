<?php

namespace App\Mcp\Tools;

use App\Models\StickerSize;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Get active mirrorcote sticker prices. Optionally filter by a size name keyword.')]
class GetStickerPriceTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $validated = $request->validate([
            'keyword' => ['nullable', 'string', 'max:100'],
        ]);

        $keyword = trim((string) ($validated['keyword'] ?? ''));

        $sizes = StickerSize::query()
            ->where('is_active', true)
            ->when($keyword !== '', fn ($query) => $query->where('name', 'like', '%'.$keyword.'%'))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get()
            ->map(fn (StickerSize $size) => [
                'id' => $size->id,
                'name' => $size->name,
                'width_cm' => $size->width_cm,
                'height_cm' => $size->height_cm,
                'price_rm' => (float) $size->price,
                'is_default' => (bool) $size->is_default,
                'material' => 'Mirrorcote',
            ])
            ->values()
            ->all();

        return Response::json([
            'material' => 'Mirrorcote',
            'count' => count($sizes),
            'sizes' => $sizes,
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'keyword' => $schema->string()->description('Optional size name keyword, e.g. "3cm" or "default"')->nullable(),
        ];
    }
}
