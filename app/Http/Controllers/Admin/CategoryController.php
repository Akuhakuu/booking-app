<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category; // <-- Model Kategori
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException; // Untuk menangani error constraint

class CategoryController extends Controller
{
    /**
     * Menampilkan halaman daftar kategori.
     */
    public function index()
    {
        return view('admin.categories.index');
    }

    /**
     * Menyediakan data kategori untuk DataTables.
     */
    public function getData(Request $request)
    {
        $categories = Category::select(['id', 'name', 'created_at']); // Hanya kolom yg dibutuhkan

        return DataTables::of($categories)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $editUrl = route('admin.categories.edit', $row->hashid); // Pakai hashid
                $deleteUrl = route('admin.categories.destroy', $row->hashid); // Pakai hashid
                return '
                <a href="' . $editUrl . '" class="btn btn-sm btn-primary">Edit</a>
                <button onclick="deleteCategory(\'' . $deleteUrl . '\')" class="btn btn-sm btn-danger">Delete</button>
                '; // Panggil JS function deleteCategory
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format('d M Y H:i') : '-';
            })
            ->rawColumns(['action']) // Render HTML di kolom action
            ->make(true);
    }

    /**
     * Menampilkan form tambah kategori baru.
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Menyimpan kategori baru ke database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah ada.',
        ]);

        try {
            Category::create($validatedData);
            return redirect()->route('admin.categories.index')
                ->with('success', 'Kategori berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Category Creation Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan kategori.');
        }
    }

    /**
     * Menampilkan form edit kategori.
     * Menggunakan Route Model Binding dengan Hashid.
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Mengupdate data kategori di database.
     */
    public function update(Request $request, Category $category)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($category->id)],
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Nama kategori sudah ada.',
        ]);

        try {
            $category->update($validatedData);
            return redirect()->route('admin.categories.index')
                ->with('success', 'Kategori berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Category Update Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui kategori.');
        }
    }

    /**
     * Menghapus kategori dari database.
     */
    public function destroy(Category $category)
    {
        // PENTING: Cek apakah kategori ini digunakan oleh Item
        if ($category->items()->exists()) {
            return response()->json(['error' => 'Kategori tidak dapat dihapus karena masih digunakan oleh item.'], 422); // 422 Unprocessable Entity
        }

        try {
            $categoryName = $category->name;
            $category->delete();
            return response()->json(['message' => "Kategori '{$categoryName}' berhasil dihapus."]);
        } catch (QueryException $e) {
            // Tangkap error spesifik jika constraint (meskipun sudah dicek di atas)
            Log::error('Category Deletion Query Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menghapus kategori karena terkait dengan data lain.'], 500);
        } catch (\Exception $e) {
            Log::error('Category Deletion Error: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menghapus kategori.'], 500);
        }
    }
}
