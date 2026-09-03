export const MIN_A3_SHEETS_WITHOUT_DESIGN = 3;
export const MIN_A3_SHEETS_WITH_DESIGN = 1;

export function minimumA3Sheets(hasDesign: boolean): number {
    return hasDesign ? MIN_A3_SHEETS_WITH_DESIGN : MIN_A3_SHEETS_WITHOUT_DESIGN;
}

export function calculateBillableA3Sheets(
    quantity: number,
    qtyPerA3: number,
    hasDesign: boolean,
): number {
    const naturalSheets = Math.ceil(quantity / Math.max(1, qtyPerA3));

    return Math.max(naturalSheets, minimumA3Sheets(hasDesign));
}
