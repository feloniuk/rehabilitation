<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\TextBlock;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::paginate(10);

        // Додаємо віртуальну "сторінку" для головної
        $homePageBlocks = TextBlock::orderBy('order')->get();

        return view('admin.pages.index', compact('pages', 'homePageBlocks'));
    }

    // Метод для редагування головної сторінки (текстових блоків)
    public function editHome()
    {
        $blocks = TextBlock::orderBy('order')->paginate(20)->withQueryString();

        return view('admin.pages.edit-home', compact('blocks'));
    }

    // Метод для оновлення текстового блоку
    public function updateBlock(Request $request, $id)
    {
        $block = TextBlock::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'type' => 'required|in:text,textarea,html',
            'order' => 'nullable|integer',
        ]);

        $block->update($request->all());

        return redirect()->route('admin.pages.edit-home')
            ->with('success', 'Текстовий блок оновлено');
    }

    // Метод для створення нового блоку
    public function createBlock()
    {
        return view('admin.pages.create-block');
    }

    public function storeBlock(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:255|unique:text_blocks',
            'title' => 'required|string|max:255',
            'content' => 'required',
            'type' => 'required|in:text,textarea,html',
            'order' => 'nullable|integer',
        ]);

        TextBlock::create($request->all());

        return redirect()->route('admin.pages.edit-home')
            ->with('success', 'Текстовий блок створено');
    }

    public function destroyBlock($id)
    {
        $block = TextBlock::findOrFail($id);
        $block->delete();

        return back()->with('success', 'Текстовий блок видалено');
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages',
            'content' => 'required|string',
        ]);

        Page::create([
            'title' => $request->title,
            'slug' => $request->slug,
            'content' => $request->content,
            'is_active' => true,
        ]);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Сторінку створено');
    }

    public function edit($id)
    {
        $page = Page::findOrFail($id);

        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:pages,slug,'.$id,
        ]);

        $page->update([
            'title' => $request->title,
            'slug' => $request->slug,
            'content' => $request->content,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('admin.pages.index')
            ->with('success', 'Сторінку оновлено');
    }

    public function destroy($id)
    {
        $page = Page::findOrFail($id);
        $page->delete();

        return back()->with('success', 'Сторінку видалено');
    }
}
