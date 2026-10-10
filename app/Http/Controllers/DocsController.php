<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Support\Docs;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocsController extends Controller
{
    public function index(): View
    {
        return view('public.docs.index', [
            'sections' => Docs::sections(),
        ]);
    }

    public function show(string $slug): View
    {
        $page = Docs::find($slug);

        abort_if($page === null, 404);

        return view('public.docs.show', [
            'page' => $page,
            'sections' => Docs::sections(),
            'sourceUrl' => Docs::sourceUrl($slug),
            'comments' => Comment::approved()
                ->where('page', $slug)
                ->whereNull('parent_id')
                ->with(['replies' => fn ($q) => $q->approved()])
                ->oldest()
                ->get(),
        ]);
    }

    public function search(Request $request): View
    {
        $query = mb_substr(trim((string) $request->query('q', '')), 0, 100);

        return view('public.docs.search', [
            'query' => $query,
            'results' => Docs::search($query),
            'sections' => Docs::sections(),
        ]);
    }
}
