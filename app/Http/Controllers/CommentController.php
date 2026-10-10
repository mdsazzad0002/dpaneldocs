<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Support\Docs;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CommentController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'page' => ['required', 'string', Rule::in(array_column(Docs::all(), 'slug'))],
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:255'],
            'body' => ['required', 'string', 'min:5', 'max:3000'],
            'website' => ['prohibited'],
        ]);

        Comment::create($data + ['status' => 'pending', 'ip_address' => $request->ip()]);

        return redirect()
            ->to(route('docs.show', $data['page']).'#comments')
            ->with('comment', 'Thanks! Your comment will appear after a quick check, and we will email you when we reply.');
    }
}
