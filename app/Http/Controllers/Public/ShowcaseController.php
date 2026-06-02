<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ShowroomItem;
use Illuminate\View\View;

class ShowcaseController extends Controller
{
    public function index(): View
    {
        $items = ShowroomItem::active()->get();
        return view('public.showcase', compact('items'));
    }
}
