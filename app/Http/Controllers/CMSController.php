<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cms;
use Illuminate\Support\Str;

class CMSController extends Controller
{
    public function index()
    {
        $pages = CMS::orderBy('created_at', 'desc')->get();
        return view('admin/cms.index', compact('pages'));
    }

    public function create()
    {
        return view('admin/cms.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'slug' => 'nullable|string|unique:cms,slug',
        ]);

        $slug = $request->input('slug', Str::slug($request->title));

        CMS::create([
            'title' => $request->title,
            'content' => $request->content,
            'slug' => $slug,
        ]);

        return redirect()->route('cms.index')->with('success', 'Page created successfully.');
    }

    public function show($id)
    {
        $page = CMS::findOrFail($id);
        return view('admin/cms.show', compact('page'));
    }

    public function edit($id)
    {
        $page = CMS::findOrFail($id);
        return view('admin/cms.edit', compact('page'));
    }

    public function update(Request $request, $id)
    {
        $page = CMS::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $page->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        return redirect()->route('cms.index')->with('success', 'Page updated successfully.');
    }

    public function destroy($id)
    {
        $page = CMS::findOrFail($id);
        $page->delete();

        return redirect()->route('cms.index')->with('success', 'Page deleted successfully.');
    }
}