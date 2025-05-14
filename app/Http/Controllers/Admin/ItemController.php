<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\File; // <-- Gunakan File facade
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException; // Untuk error handling

class ItemController extends Controller
{
    // Path target relatif di dalam folder public
    private $targetPath = 'assets/compiled/items';

    /**
     * Menampilkan halaman daftar item.
     */
    public function index()
    {
        return view('admin.items.index');
    }

    /**
     * Menyediakan data item untuk DataTables.
     */
    public function getData(Request $request)
    {
        $items = Item::with(['category', 'brand'])->select('items.*');

        return DataTables::of($items)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $editUrl = route('admin.items.edit', $row->hashid); // Menggunakan hashid
                $deleteUrl = route('admin.items.destroy', $row->hashid); // Menggunakan hashid
                return '
                <a href="' . $editUrl . '" class="btn btn-sm btn-primary">Edit</a>
                <button onclick="deleteItem(\'' . $deleteUrl . '\')" class="btn btn-sm btn-danger">Delete</button>
                ';
            })
            ->addColumn('category_name', fn($row) => $row->category?->name ?? '<span class="badge bg-light-secondary">None</span>')
            ->addColumn('brand_name', fn($row) => $row->brand?->name ?? '<span class="badge bg-light-secondary">None</span>')
            ->editColumn('rental_price', fn($row) => 'Rp ' . number_format($row->rental_price, 0, ',', '.'))
            ->addColumn('image_display', function ($row) {
                $fullPublicPath = public_path($this->targetPath . '/' . $row->img);
                if ($row->img && File::exists($fullPublicPath)) {
                    $imageUrl = asset($this->targetPath . '/' . $row->img); // URL ke public
                    return '<img src="' . $imageUrl . '" alt="' . htmlspecialchars($row->name) . '" width="60" height="60" style="object-fit: cover; border-radius: 5px;">';
                }
                return '<span class="badge bg-light-secondary">No Image</span>';
            })
            ->editColumn('status', function ($row) {
                $color = 'secondary';
                if ($row->status == 'available') $color = 'success';
                elseif ($row->status == 'rented') $color = 'warning'; // Status rented mungkin perlu logic khusus
                elseif ($row->status == 'maintenance') $color = 'info';
                elseif ($row->status == 'unavailable') $color = 'danger';
                return '<span class="badge bg-light-' . $color . '">' . ucfirst($row->status) . '</span>';
            })
            ->editColumn('created_at', fn($row) => $row->created_at?->format('d M Y H:i') ?? '-')
            ->rawColumns(['action', 'image_display', 'category_name', 'brand_name', 'status'])
            ->make(true);
    }

    /**
     * Menampilkan form tambah item baru.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->pluck('name', 'id');
        $brands = Brand::orderBy('name')->pluck('name', 'id');
        $statuses = $this->getStatuses(); // Ambil status yang bisa dipilih manual

        return view('admin.items.create', compact('categories', 'brands', 'statuses'));
    }

    /**
     * Menyimpan item baru ke database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('items', 'name')],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'rental_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'string', Rule::in(array_keys($this->getStatuses()))],
            'description' => ['nullable', 'string'],
            'img' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
        ]);

        $filenameToStore = null;
        $fullTargetPath = public_path($this->targetPath);

        if ($request->hasFile('img')) {
            try {
                if (!File::isDirectory($fullTargetPath)) {
                    File::makeDirectory($fullTargetPath, 0755, true, true);
                }
                $image = $request->file('img');
                $filenameToStore = 'item_' . time() . '_' . Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $image->getClientOriginalExtension();
                $image->move($fullTargetPath, $filenameToStore);
            } catch (\Exception $e) {
                Log::error('Item Image Upload Error (Store): ' . $e->getMessage(), ['path' => $fullTargetPath]);
                return redirect()->back()->withInput()->with('error', 'Gagal mengupload gambar item. Periksa izin folder.');
            }
        }

        try {
            Item::create(array_merge($validatedData, ['img' => $filenameToStore]));
            return redirect()->route('admin.items.index')->with('success', 'Item berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Item Creation Error: ' . $e->getMessage());
            if ($filenameToStore && File::exists($fullTargetPath . '/' . $filenameToStore)) {
                File::delete($fullTargetPath . '/' . $filenameToStore);
            }
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data item.');
        }
    }

    /**
     * Menampilkan form edit item.
     * Menggunakan Route Model Binding dengan Hashid.
     */
    public function edit(Item $item)
    {
        $categories = Category::orderBy('name')->pluck('name', 'id');
        $brands = Brand::orderBy('name')->pluck('name', 'id');
        $statuses = $this->getStatuses();
        $targetPath = $this->targetPath; // Kirim path ke view

        return view('admin.items.edit', compact('item', 'categories', 'brands', 'statuses', 'targetPath'));
    }

    /**
     * Mengupdate data item di database.
     */
    public function update(Request $request, Item $item)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('items', 'name')->ignore($item->id)],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'brand_id' => ['required', 'integer', 'exists:brands,id'],
            'rental_price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'string', Rule::in(array_keys($this->getStatuses()))],
            'description' => ['nullable', 'string'],
            'img' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg,webp', 'max:2048'],
        ]);

        $currentFilename = $item->img;
        $filenameToStore = $currentFilename;
        $fullTargetPath = public_path($this->targetPath);
        $fileUploaded = false; // Flag untuk menandai apakah file baru diupload

        if ($request->hasFile('img')) {
            try {
                if (!File::isDirectory($fullTargetPath)) {
                    File::makeDirectory($fullTargetPath, 0755, true, true);
                }

                // Hapus file lama jika ada
                $oldFilePath = $fullTargetPath . '/' . $currentFilename;
                if ($currentFilename && File::exists($oldFilePath)) {
                    File::delete($oldFilePath);
                }

                // Upload file baru
                $image = $request->file('img');
                $filenameToStore = 'item_' . time() . '_' . Str::slug(pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $image->getClientOriginalExtension();
                $image->move($fullTargetPath, $filenameToStore);
                $fileUploaded = true; // Set flag

            } catch (\Exception $e) {
                Log::error('Item Image Upload Error (Update): ' . $e->getMessage(), ['path' => $fullTargetPath]);
                return redirect()->back()->withInput()->with('error', 'Gagal memproses gambar baru. Periksa izin folder.');
            }
        }

        try {
            // Update data item di DB
            $item->update(array_merge($validatedData, ['img' => $filenameToStore]));
            return redirect()->route('admin.items.index')->with('success', 'Item berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Item Update Error: ' . $e->getMessage());
            // Jika DB gagal SETELAH file baru diupload, hapus file baru yg gagal disimpan ke DB
            if ($fileUploaded && $filenameToStore && File::exists($fullTargetPath . '/' . $filenameToStore)) {
                File::delete($fullTargetPath . '/' . $filenameToStore);
                // Optional: coba pulihkan file lama jika memungkinkan (lebih kompleks)
            }
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui data item.');
        }
    }

    /**
     * Menghapus item dari database.
     */
    public function destroy(Item $item)
    {
        // Cek relasi booking (opsional tapi bagus)
        if ($item->bookings()->exists()) {
            return response()->json(['error' => 'Item tidak dapat dihapus karena memiliki riwayat booking.'], 422);
        }

        try {
            $filenameToDelete = $item->img;
            $fullFilePath = public_path($this->targetPath . '/' . $filenameToDelete);
            $itemName = $item->name;

            // Hapus record DB dulu
            $item->delete();

            // Jika berhasil, hapus file fisik
            if ($filenameToDelete && File::exists($fullFilePath)) {
                File::delete($fullFilePath);
            }

            return response()->json(['message' => "Item '{$itemName}' berhasil dihapus."]);
        } catch (\Exception $e) {
            Log::error('Item Deletion Error: ' . $e->getMessage());
            // Tangani jika item tidak ditemukan karena hashid salah? (Meskipun route model binding harusnya sudah handle)
            if ($e instanceof ModelNotFoundException) {
                return response()->json(['error' => 'Item tidak ditemukan.'], 404);
            }
            return response()->json(['error' => 'Terjadi kesalahan saat menghapus item.'], 500);
        }
    }

    /**
     * Helper untuk mendapatkan daftar status.
     * @return array
     */
    private function getStatuses(): array
    {
        // Status yang bisa dipilih manual di form
        return ['available' => 'Available', 'maintenance' => 'Maintenance', 'unavailable' => 'Unavailable'];
    }
}
