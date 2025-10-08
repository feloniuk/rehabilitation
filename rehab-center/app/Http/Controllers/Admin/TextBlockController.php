<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TextBlock;
use Illuminate\Http\Request;

class TextBlockController extends Controller
{
    public function index()
    {
        $blocks = TextBlock::orderBy('order')->paginate(20);
        return view('admin.text-blocks.index', compact('blocks'));
    }

    public function create()
    {
        return view('admin.text-blocks.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:255|unique:text_blocks',
            'title' => 'required|string|max:255',
            'content' => 'required',
            'type' => 'required|in:text,textarea,html',
            'order' => 'nullable|integer',
        ]);

        TextBlock::create($request->all());

        return redirect()->route('admin.text-blocks.index')
                        ->with('success', 'Текстовий блок створено');
    }

    public function edit($id)
    {
        $block = TextBlock::findOrFail($id);
        return view('admin.text-blocks.edit', compact('block'));
    }

    public function update(Request $request, $id)
    {
        $block = TextBlock::findOrFail($id);

        $request->validate([
            'key' => 'required|string|max:255|unique:text_blocks,key,' . $id,
            'title' => 'required|string|max:255',
            'content' => 'required',
            'type' => 'required|in:text,textarea,html',
            'order' => 'nullable|integer',
        ]);

        $block->update($request->all());

        return redirect()->route('admin.text-blocks.index')
                        ->with('success', 'Текстовий блок оновлено');
    }

    public function destroy($id)
    {
        $block = TextBlock::findOrFail($id);
        $block->delete();

        return redirect()->route('admin.text-blocks.index')
                        ->with('success', 'Текстовий блок видалено');
    }
}