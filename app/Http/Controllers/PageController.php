<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Support\Docs;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'sections' => Docs::sections(),
            'reviews' => Review::approved()->where('rating', '>=', 4)->latest('approved_at')->limit(3)->get(),
            'rating' => Review::summary(),
        ]);
    }

    public function privacy(): View
    {
        return view('public.legal.privacy');
    }

    public function terms(): View
    {
        return view('public.legal.terms');
    }
}
