<?php

namespace App\Http\Controllers;

use App\Models\Tool;
use App\Models\ToolCategory;
use Illuminate\Http\Request;

class ToolController extends Controller
{
    public function index(Request $request)
    {
        $query = Tool::query()->with('category')->active();

        if ($search = $request->query('search')) {
            $escaped = addcslashes($search, '%_');
            $query->where(function ($q) use ($escaped) {
                $q->where('name', 'like', "%{$escaped}%")
                    ->orWhere('code', 'like', "%{$escaped}%")
                    ->orWhere('brand', 'like', "%{$escaped}%")
                    ->orWhere('description', 'like', "%{$escaped}%");
            });
        }

        if ($category = $request->query('category')) {
            $query->whereHas('category', fn ($q) => $q->where('name', $category));
        }

        $tools = $query->orderBy('name')->paginate(8)->withQueryString();
        $categories = ToolCategory::orderBy('name')->get();

        return view('tools.index', compact('tools', 'categories'));
    }

    public function show(Tool $tool)
    {
        abort_unless($tool->is_active, 404);

        $tool->load('category');

        $related = Tool::active()->with('category')
            ->where('category_id', $tool->category_id)
            ->where('id', '!=', $tool->id)
            ->limit(3)
            ->get();

        return view('tools.show', compact('tool', 'related'));
    }
}
