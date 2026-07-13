<?php

namespace App\Http\Controllers\Academic;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = $request->string('q')->toString();
                $query->where(function ($sub) use ($q) {
                    $sub->where('code', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return view('academic.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('academic.categories.create', ['category' => new Category()]);
    }

    public function store(Request $request): RedirectResponse
    {
        Category::create($this->validated($request));

        return redirect()->route('academic.categories.index')->with('status', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('academic.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));

        return redirect()->route('academic.categories.index')->with('status', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $category->delete();

        return redirect()->route('academic.categories.index')->with('status', 'Category deleted.');
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $categoryId = $category?->category_id;

        return $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('categories', 'code')->ignore($categoryId, 'category_id')],
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'is_reserved' => ['boolean'],
            'reservation_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['boolean'],
        ]);
    }
}
