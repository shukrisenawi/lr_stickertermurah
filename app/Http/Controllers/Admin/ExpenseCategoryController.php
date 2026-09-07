<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseCategoryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/ExpenseCategories/Index', [
            'categories' => ExpenseCategory::query()
                ->withCount('expenses')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('expense_categories', 'name')],
        ]);

        ExpenseCategory::query()->create($validated);

        return redirect()->route('admin.expense-categories.index')->with('success', 'Kategori duit keluar berjaya ditambah.');
    }

    public function update(Request $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_categories', 'name')->ignore($expenseCategory->id),
            ],
        ]);

        $expenseCategory->update($validated);

        return redirect()->route('admin.expense-categories.index')->with('success', 'Kategori duit keluar berjaya dikemaskini.');
    }

    public function destroy(ExpenseCategory $expenseCategory): RedirectResponse
    {
        $expenseCategory->delete();

        return redirect()->route('admin.expense-categories.index')->with('success', 'Kategori duit keluar berjaya dipadam.');
    }
}
