<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Item;     // Model Item
use App\Models\CartItem; // Model CartItem
use Vinkla\Hashids\Facades\Hashids; // Facade Hashids
use Illuminate\Support\Facades\Auth; // Facade Auth
use Illuminate\View\View;            // Type hint View
use Illuminate\Support\Facades\Log;    // Facade Log
use Illuminate\Support\Facades\DB;     // Facade DB untuk transaksi
use Illuminate\Database\Eloquent\ModelNotFoundException; // Exception

class CartController extends Controller
{
    /**
     * Batas maksimal quantity per jenis item di keranjang.
     */
    private const MAX_CART_QUANTITY_PER_ITEM = 100;

    /**
     * Terapkan middleware auth:customer ke semua method controller ini.
     */
    public function __construct()
    {
        $this->middleware('auth:customer');
    }

    /**
     * Menampilkan halaman keranjang belanja.
     * Mengambil data CartItem dari database untuk customer yang login.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $customer = Auth::guard('customer')->user();
        /** @var \App\Models\Customer $customer */

        // Ambil item cart milik customer, eager load relasi item
        $cartItems = $customer->cartItems()->with(['item'])->latest()->get();

        // Hitung total harga HANYA untuk estimasi tampilan awal (perhitungan final saat checkout)
        $totalPrice = 0;
        foreach ($cartItems as $cartItem) {
            if ($cartItem->item) { // Pastikan item masih ada
                $totalPrice += $cartItem->item->rental_price * $cartItem->quantity;
            }
        }

        // Kirim data ke view keranjang
        return view('customer.cart.index', compact('cartItems', 'totalPrice'));
    }

    /**
     * Menambahkan item ke keranjang belanja (database).
     * Menerima hashid Item dari request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function add(Request $request)
    {
        // 1. Validasi Input Request
        $request->validate([
            'item_id' => 'required|string', // Menerima hashid Item
            'quantity' => 'required|integer|min:1',
        ]);

        // 2. Decode Hashid Item
        $decodedItemId = Hashids::decode($request->input('item_id'));
        if (empty($decodedItemId)) {
            return back()->with('error', 'Format ID item tidak valid.');
        }
        $itemId = $decodedItemId[0]; // ID asli item
        $quantityToAdd = (int) $request->input('quantity');
        $customer = Auth::guard('customer')->user();
        /** @var \App\Models\Customer $customer */

        // 3. Cari Item Master
        $item = Item::find($itemId);

        // 4. Validasi Item Master
        if (!$item) return back()->with('error', 'Item tidak ditemukan.');
        if ($item->status !== 'available') return back()->with('error', 'Item "' . $item->name . '" sedang tidak tersedia untuk disewa.');
        if ($item->stock <= 0) return back()->with('error', 'Stok item "' . $item->name . '" habis.');
        if ($quantityToAdd > $item->stock) return back()->with('error', 'Jumlah (' . $quantityToAdd . ') melebihi stok (' . $item->stock . ') untuk item "' . $item->name . '".');
        if ($quantityToAdd > self::MAX_CART_QUANTITY_PER_ITEM) return back()->with('error', 'Maksimal ' . self::MAX_CART_QUANTITY_PER_ITEM . ' unit per item di keranjang.');


        // 5. Mulai Transaksi Database
        DB::beginTransaction();
        try {
            // Cari apakah item sudah ada di keranjang customer ini (berdasarkan ID asli item)
            $cartItem = $customer->cartItems()->where('item_id', $itemId)->first();

            if ($cartItem) {
                // --- Item Sudah Ada di Keranjang ---
                $newQuantity = $cartItem->quantity + $quantityToAdd;

                // Cek Batas Maksimal Keranjang (100)
                if ($newQuantity > self::MAX_CART_QUANTITY_PER_ITEM) {
                    DB::rollBack();
                    return back()->with('error', 'Maksimal ' . self::MAX_CART_QUANTITY_PER_ITEM . ' unit per item di keranjang.');
                }
                // Cek Stok Tersedia (lagi, untuk newQuantity total)
                if ($newQuantity > $item->stock) {
                    DB::rollBack();
                    return back()->with('error', 'Jumlah total di keranjang (' . $newQuantity . ') melebihi stok (' . $item->stock . ') untuk item "' . $item->name . '".');
                }

                // Update quantity di record CartItem yang ada
                $cartItem->quantity = $newQuantity;
                $cartItem->save();
            } else {
                // --- Item Baru di Keranjang ---
                // Validasi untuk item baru sudah dilakukan di atas (quantityToAdd vs stock & MAX_CART_QUANTITY_PER_ITEM)
                // Buat record CartItem baru
                $customer->cartItems()->create([
                    'item_id' => $itemId, // Simpan ID asli item
                    'quantity' => $quantityToAdd,
                ]);
            }

            DB::commit(); // Simpan semua perubahan ke database
            return redirect()->back()->with('success', 'Item berhasil ditambahkan ke keranjang!');
        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan transaksi jika terjadi error
            Log::error('Error adding item to cart (DB): ' . $e->getMessage(), ['item_id' => $itemId, 'customer_id' => $customer->id]);
            return back()->with('error', 'Gagal menambahkan item ke keranjang. Silakan coba lagi.');
        }
    }

    /**
     * Memperbarui jumlah item di keranjang (database).
     * Menerima HASHID dari CartItem dari request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        // 1. Validasi Input Request
        $request->validate([
            'cart_item_hashid' => 'required|string', // Menerima hashid CartItem
            'quantity' => 'required|integer|min:1',
        ]);

        // 2. Decode Hashid CartItem
        $decodedCartItemId = Hashids::decode($request->input('cart_item_hashid'));
        if (empty($decodedCartItemId)) {
            if ($request->expectsJson()) return response()->json(['error' => 'Item keranjang tidak valid.'], 400);
            return redirect()->route('customer.cart.index')->with('error', 'Item keranjang tidak valid.');
        }
        $cartItemId = $decodedCartItemId[0]; // ID asli CartItem
        $newQuantity = (int) $request->input('quantity');
        $customer = Auth::guard('customer')->user();
        /** @var \App\Models\Customer $customer */

        // 3. Mulai Transaksi Database
        DB::beginTransaction();
        try {
            // Cari CartItem berdasarkan ID asli, pastikan milik customer yg login, dan load item terkait
            $cartItem = $customer->cartItems()->with('item')->findOrFail($cartItemId);

            // Cek Batas Maksimal Keranjang (100)
            if ($newQuantity > self::MAX_CART_QUANTITY_PER_ITEM) {
                DB::rollBack();
                if ($request->expectsJson()) return response()->json(['error' => 'Jumlah maksimal per item (' . self::MAX_CART_QUANTITY_PER_ITEM . ') terlampaui.'], 422);
                return redirect()->route('customer.cart.index')->with('error', 'Jumlah maksimal per item (' . self::MAX_CART_QUANTITY_PER_ITEM . ') terlampaui.');
            }

            // Cek Item Master dan Stoknya
            if (!$cartItem->item) {
                DB::rollBack();
                $cartItem->delete(); // Hapus item cart jika item aslinya tidak ada
                if ($request->expectsJson()) return response()->json(['error' => 'Item asli tidak ditemukan dan telah dihapus dari keranjang.'], 404);
                return redirect()->route('customer.cart.index')->with('warning', 'Item asli tidak ditemukan dan telah dihapus dari keranjang.');
            }
            if ($cartItem->item->stock < $newQuantity) {
                DB::rollBack();
                if ($request->expectsJson()) return response()->json(['error' => 'Jumlah (' . $newQuantity . ') melebihi stok (' . $cartItem->item->stock . ') untuk item "' . $cartItem->item->name . '".'], 422);
                return redirect()->route('customer.cart.index')->with('error', 'Jumlah (' . $newQuantity . ') melebihi stok (' . $cartItem->item->stock . ') untuk item "' . $cartItem->item->name . '".');
            }
            if ($cartItem->item->status !== 'available') {
                DB::rollBack();
                if ($request->expectsJson()) return response()->json(['error' => 'Item "' . $cartItem->item->name . '" sedang tidak tersedia.'], 422);
                return redirect()->route('customer.cart.index')->with('error', 'Item "' . $cartItem->item->name . '" sedang tidak tersedia.');
            }

            // Update quantity pada record CartItem
            $cartItem->quantity = $newQuantity;
            $cartItem->save();

            DB::commit(); // Simpan perubahan

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Jumlah item diperbarui.']);
            }
            return redirect()->route('customer.cart.index')->with('success', 'Jumlah item diperbarui.');
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            if ($request->expectsJson()) return response()->json(['error' => 'Item keranjang tidak ditemukan.'], 404);
            return redirect()->route('customer.cart.index')->with('error', 'Item keranjang tidak ditemukan.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating cart item (DB): ' . $e->getMessage(), ['cart_item_id' => $cartItemId, 'customer_id' => $customer->id]);
            if ($request->expectsJson()) return response()->json(['error' => 'Gagal memperbarui jumlah item.'], 500);
            return redirect()->route('customer.cart.index')->with('error', 'Gagal memperbarui jumlah item.');
        }
    }

    /**
     * Menghapus item dari keranjang (database).
     * Menerima HASHID dari CartItem dari request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function remove(Request $request)
    {
        // 1. Validasi Input Request
        $request->validate([
            'cart_item_hashid' => 'required|string', // Menerima hashid CartItem
        ]);

        // 2. Decode Hashid CartItem
        $decodedCartItemId = Hashids::decode($request->input('cart_item_hashid'));
        if (empty($decodedCartItemId)) {
            if ($request->expectsJson()) return response()->json(['error' => 'Item keranjang tidak valid.'], 400);
            return redirect()->route('customer.cart.index')->with('error', 'Item keranjang tidak valid.');
        }
        $cartItemId = $decodedCartItemId[0]; // ID asli CartItem
        $customer = Auth::guard('customer')->user();
        /** @var \App\Models\Customer $customer */

        try {
            // 3. Cari CartItem berdasarkan ID asli dan pastikan milik customer yg login
            $cartItem = $customer->cartItems()->findOrFail($cartItemId);
            // Ambil nama item sebelum dihapus (untuk pesan)
            $itemName = optional($cartItem->item)->name ?? 'Item'; // Gunakan optional chaining

            // 4. Hapus record CartItem
            $cartItem->delete();

            if ($request->expectsJson()) {
                return response()->json(['message' => "{$itemName} berhasil dihapus dari keranjang."]);
            }
            return redirect()->route('customer.cart.index')->with('success', "{$itemName} berhasil dihapus dari keranjang.");
        } catch (ModelNotFoundException $e) {
            // Jika cart item tidak ditemukan
            if ($request->expectsJson()) return response()->json(['error' => 'Item keranjang tidak ditemukan.'], 404);
            return redirect()->route('customer.cart.index')->with('warning', 'Item keranjang sudah tidak ditemukan.');
        } catch (\Exception $e) {
            Log::error('Error removing cart item (DB): ' . $e->getMessage(), ['cart_item_id' => $cartItemId, 'customer_id' => $customer->id]);
            if ($request->expectsJson()) return response()->json(['error' => 'Gagal menghapus item dari keranjang.'], 500);
            return redirect()->route('customer.cart.index')->with('error', 'Gagal menghapus item dari keranjang.');
        }
    }

    // Method checkout() akan berada di BookingController
}
