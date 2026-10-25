<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IncomeCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class IncomeCategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/IncomeCategories/Index', [
            'categories' => IncomeCategory::query()->withCount('incomes')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('income_categories', 'name')],
        ]);
        IncomeCategory::query()->create($validated);

        return redirect()->route('admin.income-categories.index')->with('success', 'Kategori duit masuk berjaya ditambah.');
    }

    public function update(Request $request, IncomeCategory $incomeCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('income_categories', 'name')->ignore($incomeCategory->id)],
        ]);
        $incomeCategory->update($validated);

        return redirect()->route('admin.income-categories.index')->with('success', 'Kategori duit masuk berjaya dikemaskini.');
    }

    public function destroy(IncomeCategory $incomeCategory): RedirectResponse
    {
        $incomeCategory->delete();

        return redirect()->route('admin.income-categories.index')->with('success', 'Kategori duit masuk berjaya dipadam.');
    }
}
