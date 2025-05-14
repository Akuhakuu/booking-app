<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand; // Model Brand Anda
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log; // Import Log facade
use Illuminate\Database\QueryException;
use Vinkla\Hashids\Facades\Hashids; // <-- IMPORT HASHIDS FACADE
use Illuminate\Database\Eloquent\ModelNotFoundException; // Untuk handle findOrFail

class BrandController extends Controller
{
    /**
     * Menampilkan halaman utama daftar brand.
     */
    public function index()
    {
        return view('admin.brands.index'); // Pastikan path view benar
    }

    /**
     * Menyediakan data untuk DataTables.
     */
    public function getData(Request $request)
    {
        $brands = Brand::select(['id', 'name', 'created_at']);

        return DataTables::of($brands)
            ->addIndexColumn()
            ->addColumn('action', function (Brand $row) {
                $brandHash = $row->hashid; // 1. Mengambil hashid dari model Brand

                // 2. Pengecekan apakah $brandHash kosong
                if (empty($brandHash)) {
                    // Jika $row->hashid menghasilkan null atau string kosong,
                    // kode di dalam blok if ini akan dijalankan.
                    Log::error("HashID kosong untuk Brand ID: {$row->id}"); // Mencatat error di log
                    return '<span class="text-danger">Error ID</span>'; // <--- INI YANG MENYEBABKAN TULISAN MUNCUL
                }

                // Kode ini HANYA akan dijalankan jika $brandHash TIDAK kosong:
                $editUrl = route('admin.brands.edit', ['brand_hash' => $brandHash]);
                $brandHashForJs = $brandHash;

                return '
                <a href="' . $editUrl . '" class="btn btn-sm btn-primary">Edit</a>
                <button onclick="deleteBrand(\'' . $brandHashForJs . '\')" class="btn btn-sm btn-danger">Delete</button>
                ';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format('d M Y H:i') : '-';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Menampilkan form untuk membuat brand baru.
     */
    public function create()
    {
        return view('admin.brands.create'); // Pastikan path view benar
    }

    /**
     * Menyimpan brand baru ke database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('brands', 'name')],
        ], [
            'name.required' => 'Nama brand wajib diisi.',
            'name.unique' => 'Nama brand sudah ada.',
        ]);

        try {
            Brand::create($validatedData);
            return redirect()->route('admin.brands.index')
                ->with('success', 'Brand berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error creating brand: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menambahkan brand.');
        }
    }

    /**
     * Menampilkan form untuk mengedit brand.
     * Menerima hashid sebagai parameter string.
     */
    public function edit($brand_hash) // <-- Parameter adalah hash string
    {
        $decodedId = Hashids::decode($brand_hash); // <-- Decode hashid

        // Cek jika decode gagal atau array kosong
        if (empty($decodedId)) {
            abort(404, 'Brand tidak ditemukan.');
        }

        $id = $decodedId[0]; // Ambil ID asli dari hasil decode

        try {
            $brand = Brand::findOrFail($id); // Cari berdasarkan ID asli
            // Pastikan path view benar
            return view('admin.brands.edit', compact('brand'));
        } catch (ModelNotFoundException $e) {
            // Handle jika ID hasil decode tidak ada di DB
            abort(404, 'Brand tidak ditemukan.');
        }
    }

    /**
     * Mengupdate data brand di database.
     * Menerima hashid sebagai parameter string.
     */
    public function update(Request $request, $brand_hash) // <-- Parameter adalah hash string
    {
        $decodedId = Hashids::decode($brand_hash); // <-- Decode hashid

        // Cek jika decode gagal atau array kosong
        if (empty($decodedId)) {
            abort(404, 'Brand tidak ditemukan.');
        }

        $id = $decodedId[0]; // Ambil ID asli

        // Validasi input, gunakan ID asli untuk ignore rule
        $validatedData = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name')->ignore($id), // <-- Ignore ID asli
            ],
        ], [
            'name.required' => 'Nama brand wajib diisi.',
            'name.unique' => 'Nama brand sudah ada.',
        ]);

        try {
            $brand = Brand::findOrFail($id); // Cari berdasarkan ID asli
            $brand->update($validatedData); // Update data

            return redirect()->route('admin.brands.index')
                ->with('success', 'Brand berhasil diperbarui.');
        } catch (ModelNotFoundException $e) {
            // Handle jika ID hasil decode tidak ada di DB
            abort(404, 'Brand tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error('Error updating brand: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui brand.');
        }
    }

    /**
     * Menghapus brand dari database.
     * Menerima hashid sebagai parameter string.
     */
    public function destroy($brand_hash) // <-- Parameter adalah hash string
    {
        $decodedId = Hashids::decode($brand_hash); // <-- Decode hashid

        // Cek jika decode gagal atau array kosong
        if (empty($decodedId)) {
            // Respon JSON karena dipanggil via AJAX
            return response()->json(['error' => 'Format ID Brand tidak valid.'], 400); // Bad Request
        }

        $id = $decodedId[0]; // Ambil ID asli

        try {
            $brand = Brand::findOrFail($id); // Cari berdasarkan ID asli

            // Cek relasi (PENTING!)
            if ($brand->items()->exists()) { // Asumsi ada relasi items() di model Brand
                return response()->json(['error' => 'Brand tidak dapat dihapus karena masih digunakan oleh item.'], 422); // Unprocessable Entity
            }

            $brandName = $brand->name;
            $brand->delete();

            return response()->json(['message' => "Brand '{$brandName}' berhasil dihapus."]);
        } catch (ModelNotFoundException $e) {
            // Handle jika ID hasil decode tidak ada di DB
            return response()->json(['error' => 'Brand tidak ditemukan.'], 404); // Not Found
        } catch (QueryException $e) {
            Log::error('Brand Deletion Query Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menghapus brand karena terkait data lain.'], 500);
        } catch (\Exception $e) {
            Log::error('Error deleting brand: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menghapus brand.'], 500);
        }
    }
}
