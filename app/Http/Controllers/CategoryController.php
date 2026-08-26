<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::withCount('documentation')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'post_count' => $category->documentation_count,
            ]);

        return Inertia::render('Backend/Documentation/Categories', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:64', Rule::unique('categories', 'name')],
        ]);

        Category::create([
            'id' => (string) Str::uuid(),
            'name' => $validated['name'],
            'slug' => $this->uniqueSlug($validated['name']),
        ]);

        return redirect()->route('categories.index')->with('success', "Category '{$validated['name']}' created.");
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:64', Rule::unique('categories', 'name')->ignore($category->id)],
        ]);

        if ($category->name !== $validated['name']) {
            $category->slug = $this->uniqueSlug($validated['name'], $category->id);
        }

        $category->name = $validated['name'];
        $category->save();

        return redirect()->route('categories.index')->with('success', "Category '{$validated['name']}' updated.");
    }

    public function destroy(string $id): RedirectResponse
    {
        $category = Category::findOrFail($id);
        $name = $category->name;
        $category->delete();

        return redirect()->route('categories.index')->with('success', "Category '{$name}' deleted.");
    }

    private function uniqueSlug(string $name, ?string $ignoreId = null): string
    {
        return Category::uniqueSlug($name, $ignoreId);
    }
}
