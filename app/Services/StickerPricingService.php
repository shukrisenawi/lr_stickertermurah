<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\PriceSetting;
use App\Models\Setting;
use App\Models\StickerSize;

class StickerPricingService
{
    public const MIN_A3_SHEETS_WITHOUT_DESIGN = 3;

    public const MIN_A3_SHEETS_WITH_DESIGN = 1;

    public const MIN_A3_SHEETS_SETTING_KEY = 'minimum_a3_sheets_without_design';

    private ?int $configuredMinimumA3SheetsWithoutDesign = null;

    public function minimumA3SheetsWithoutDesign(): int
    {
        return $this->configuredMinimumA3SheetsWithoutDesign ??= max(
            1,
            (int) Setting::getValue(self::MIN_A3_SHEETS_SETTING_KEY, self::MIN_A3_SHEETS_WITHOUT_DESIGN),
        );
    }

    public function minimumA3Sheets(bool $hasDesign): int
    {
        return $hasDesign
            ? self::MIN_A3_SHEETS_WITH_DESIGN
            : $this->minimumA3SheetsWithoutDesign();
    }

    public function a3Sheets(int $quantity, int $qtyPerA3, bool $hasDesign): int
    {
        return max(
            (int) ceil($quantity / max(1, $qtyPerA3)),
            $this->minimumA3Sheets($hasDesign),
        );
    }

    public function hasDesign(
        ?int $designId = null,
        ?int $projectId = null,
        ?int $previousOrderItemId = null,
        array $customerDesignPaths = [],
    ): bool {
        return $designId !== null
            || $projectId !== null
            || $previousOrderItemId !== null
            || $customerDesignPaths !== [];
    }

    public function hasExistingDesign(OrderItem $item): bool
    {
        return $this->hasDesign(
            $item->sticker_design_id,
            $item->customer_project_id,
            null,
            $this->existingDesignPaths($item),
        );
    }

    public function stickerDescription(OrderItem $item): string
    {
        $size = trim((string) $item->size?->name);
        if ($size === '') {
            $size = trim((string) $item->requested_size);
        }

        $size = preg_replace('/^Saiz:\s*/iu', '', $size) ?? $size;
        $size = preg_replace('/^(?:Bulat|Petak|Segi Empat Sama|Lain-lain):\s*/iu', '', $size) ?? $size;

        return $size === '' ? 'Sticker' : "Sticker : {$size}";
    }

    public function existingDesignPaths(OrderItem $item): array
    {
        return collect([
            $item->customer_design_paths,
            $item->customer_design_path,
            $item->admin_source_paths,
            $item->admin_source_path,
            $item->customer_preview_paths,
            $item->customer_preview_path,
        ])->flatten()->filter()->values()->all();
    }

    public function a3Description(OrderItem $item): ?string
    {
        $qtyPerA3 = $item->quoted_qty_per_a3 ?: $item->size?->qty_per_a3;
        if (! $qtyPerA3) {
            return null;
        }

        $hasDesign = $this->hasExistingDesign($item);
        $a3Sheets = $this->a3Sheets((int) $item->quantity, (int) $qtyPerA3, $hasDesign);
        $minimumNote = $hasDesign
            ? null
            : " (minimum {$this->minimumA3Sheets(false)} tanpa design)";
        $quotedRate = $item->quoted_qty_per_a3 && $item->quoted_price_per_a3
            ? ' • '.$item->quoted_qty_per_a3.' pcs/A3 @ RM'.number_format((float) $item->quoted_price_per_a3, 2).'/A3'
            : '';

        return "Kiraan: {$a3Sheets} helai A3{$minimumNote}{$quotedRate}";
    }

    public function priceFor(string $stickerType, int $a3Sheets): ?PriceSetting
    {
        return PriceSetting::query()
            ->where('is_active', true)
            ->where('sticker_type', $stickerType)
            ->where('qty_from', '<=', $a3Sheets)
            ->where(function ($query) use ($a3Sheets): void {
                $query->where('qty_to', '>=', $a3Sheets)
                    ->orWhereNull('qty_to');
            })
            ->orderBy('qty_from')
            ->first();
    }

    /** @return array{a3_sheets: int, price_per_a3: float, line_total: float}|null */
    public function calculate(
        ?StickerSize $size,
        int $quantity,
        bool $hasDesign,
        string $stickerType = 'Mirrorcote',
    ): ?array {
        if (! $size?->qty_per_a3) {
            return null;
        }

        $a3Sheets = $this->a3Sheets($quantity, (int) $size->qty_per_a3, $hasDesign);
        $priceSetting = $this->priceFor($stickerType, $a3Sheets);

        if (! $priceSetting) {
            return null;
        }

        $pricePerA3 = (float) $priceSetting->price_per_a3;

        return [
            'a3_sheets' => $a3Sheets,
            'price_per_a3' => $pricePerA3,
            'line_total' => round($a3Sheets * $pricePerA3, 2),
        ];
    }
}
