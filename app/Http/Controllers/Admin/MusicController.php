<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Music;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MusicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $musicItems = Music::withCount('stories')
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return view('admin.music.index', compact('musicItems'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return redirect()->route('admin.music.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'file_upload' => ['required', 'file', 'max:102400'],
        ]);

        if ($request->hasFile('file_upload')) {
            $path = $request->file('file_upload')->store('music', 'public');
            $data['file'] = Storage::url($path);
        }

        unset($data['file_upload']);

        Music::create($data);

        return redirect()->route('admin.music.index')->with('status', 'Muzika je dodana.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Music $music)
    {
        return redirect()->route('admin.music.edit', $music);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Music $music)
    {
        $music->loadCount('stories');

        return view('admin.music.edit', compact('music'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Music $music)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'file_upload' => ['nullable', 'file', 'max:102400'],
        ]);

        if ($request->hasFile('file_upload')) {
            $path = $request->file('file_upload')->store('music', 'public');
            $data['file'] = Storage::url($path);
        }

        unset($data['file_upload']);

        $music->update($data);

        return redirect()->route('admin.music.index')->with('status', 'Muzika je azurirana.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Music $music)
    {
        $music->delete();

        return redirect()->route('admin.music.index')->with('status', 'Muzika je obrisana.');
    }

}
