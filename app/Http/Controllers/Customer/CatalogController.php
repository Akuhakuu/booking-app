<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Item;
use Illuminate\View\View;
use Vinkla\Hashids\Facades\Hashids; // <-- Import Hashids
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CatalogController extends Controller
{
    /**
     * Menampilkan halaman katalog produk/item.
     */
    public function index(Request $request): View
    {
        $categories = Category::orderBy('name')->get();
        $selectedCategory = null;
        $searchQuery = $request->input('search');
        $categoryHashid = $request->input('category');

        $itemsQuery = Item::with(['category', 'brand'])
            ->where('status', 'available')
            ->where('stock', '>', 0);

        // Filter Kategori (Decode Manual)
        if ($categoryHashid) {
            $decodedId = Hashids::decode($categoryHashid);
            if (!empty($decodedId)) {
                $categoryId = $decodedId[0];
                $selectedCategory = Category::find($categoryId);
                if ($selectedCategory) {
                    $itemsQuery->where('category_id', $selectedCategory->id);
                }
            }
        }

        // Filter Pencarian
        if ($searchQuery) {
            $itemsQuery->where(function ($query) use ($searchQuery) {
                $query->where('name', 'like', '%' . $searchQuery . '%')
                    ->orWhere('description', 'like', '%' . $searchQuery . '%')
                    ->orWhereHas('category', fn($q) => $q->where('name', 'like', '%' . $searchQuery . '%'))
                    ->orWhereHas('brand', fn($q) => $q->where('name', 'like', '%' . $searchQuery . '%'));
            });
        }

        $items = $itemsQuery->latest()->get();

        return view('customer.catalog.index', compact('categories', 'items', 'selectedCategory', 'searchQuery'));
    }

    /**
     * Menampilkan halaman detail item (Decode Manual).
     *
     * @param string $item_hash Hashid dari item
     * @return View|\Illuminate\Http\RedirectResponse
     */
    public function show($item_hash) // <-- Menerima HASH STRING
    {
        // 1. Decode Hashid
        $decodedId = Hashids::decode($item_hash);

        // 2. Cek Hasil Decode
        if (empty($decodedId)) {
            Log::warning("Invalid item hashid format provided in show: " . $item_hash);
            abort(404, 'Item tidak ditemukan.');
        }
        $id = $decodedId[0]; // Ambil ID asli

        // 3. Cari Item berdasarkan ID asli (langsung pakai findOrFail)
        try {
            // Eager load relasi saat mencari
            $item = Item::with(['category', 'brand'])->findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::warning("Item not found for decoded ID in show: " . $id);
            abort(404, 'Item tidak ditemukan.');
        }

        // 4. Cek Status & Stok (Logika Bisnis)
        if ($item->status !== 'available' || $item->stock <= 0) {
            return redirect()->route('customer.catalog.index')
                ->with('warning', 'Item "' . $item->name . '" tidak tersedia saat ini.');
        }

        // 5. Kembalikan View
        return view('customer.catalog.show', compact('item'));
    }
}
