export const MIN_A3_SHEETS_WITHOUT_DESIGN = 3;
export const MIN_A3_SHEETS_WITH_DESIGN = 1;

export function minimumA3Sheets(hasDesign: boolean, minimumWithoutDesign = MIN_A3_SHEETS_WITHOUT_DESIGN): number {
    if (hasDesign) return MIN_A3_SHEETS_WITH_DESIGN;

    return Number.isFinite(minimumWithoutDesign)
        ? Math.max(1, Math.floor(minimumWithoutDesign))
        : MIN_A3_SHEETS_WITHOUT_DESIGN;
}

export function calculateBillableA3Sheets(
    quantity: number,
    qtyPerA3: number,
    hasDesign: boolean,
    minimumWithoutDesign = MIN_A3_SHEETS_WITHOUT_DESIGN,
): number {
    const naturalSheets = Math.ceil(quantity / Math.max(1, qtyPerA3));

    return Math.max(naturalSheets, minimumA3Sheets(hasDesign, minimumWithoutDesign));
}
