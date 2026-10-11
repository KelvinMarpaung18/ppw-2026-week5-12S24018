<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KategoriController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(): View
    {
        $kategoris = Kategori::withCount('bukus')
            ->orderBy('kode_kategori', 'asc')
            ->paginate(10);

        return view('kategori.index', compact('kategoris'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        return view('kategori.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kategori' => ['required', 'string', 'min:2', 'max:20', 'unique:kategoris,kode_kategori'],
            'nama_kategori' => ['required', 'string', 'min:3', 'max:100'],
        ], [
            'kode_kategori.required' => 'Kode kategori wajib diisi.',
            'kode_kategori.unique' => 'Kode kategori sudah terdaftar.',
            'kode_kategori.min' => 'Kode kategori minimal 2 karakter.',
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.min' => 'Nama kategori minimal 3 karakter.',
        ]);

        Kategori::create($validated);

        return redirect()
            ->route('kategori.index')
            ->with('success', 'Kategori baru berhasil ditambahkan!');
    }

    /**
     * Display the specified category along with its books.
     */
    public function show(Kategori $kategori): View
    {
        $kategori->load('bukus');

        return view('kategori.show', compact('kategori'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Kategori $kategori): View
    {
        return view('kategori.edit', compact('kategori'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Kategori $kategori): RedirectResponse
    {
        $validated = $request->validate([
            'kode_kategori' => [
                'required',
                'string',
                'min:2',
                'max:20',
                Rule::unique('kategoris', 'kode_kategori')->ignore($kategori->id),
            ],
            'nama_kategori' => ['required', 'string', 'min:3', 'max:100'],
        ], [
            'kode_kategori.required' => 'Kode kategori wajib diisi.',
            'kode_kategori.unique' => 'Kode kategori sudah digunakan oleh kategori lain.',
            'kode_kategori.min' => 'Kode kategori minimal 2 karakter.',
            'nama_kategori.required' => 'Nama kategori wajib diisi.',
            'nama_kategori.min' => 'Nama kategori minimal 3 karakter.',
        ]);

        $kategori->update($validated);

        return redirect()
            ->route('kategori.index')
            ->with('success', 'Data kategori berhasil diperbarui!');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Kategori $kategori): RedirectResponse
    {
        $namaKategori = $kategori->nama_kategori;
        $kategori->delete();

        return redirect()
            ->route('kategori.index')
            ->with('success', "Kategori '{$namaKategori}' beserta seluruh buku terkait berhasil dihapus!");
    }
}
