<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer; // <-- Model Customer
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password; // Untuk aturan password yang lebih kuat
use Illuminate\Support\Facades\Hash; // Untuk hashing password
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class CustomerController extends Controller
{
    /**
     * Menampilkan halaman daftar customer.
     */
    public function index()
    {
        return view('admin.customers.index');
    }

    /**
     * Menyediakan data customer untuk DataTables.
     */
    public function getData(Request $request)
    {
        // Pilih kolom yang dibutuhkan
        $customers = Customer::select([
            'id',
            'name',
            'email',
            'phone_number',
            'status',
            'created_at'
        ]);

        return DataTables::of($customers)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                $editUrl = route('admin.customers.edit', $row->hashid);
                $deleteUrl = route('admin.customers.destroy', $row->hashid);
                return '
                <a href="' . $editUrl . '" class="btn btn-sm btn-primary">Edit</a>
                <button onclick="deleteCustomer(\'' . $deleteUrl . '\')" class="btn btn-sm btn-danger">Delete</button>
                '; // Panggil JS function deleteCustomer
            })
            ->editColumn('status', function ($row) {
                $color = $row->status === 'active' ? 'success' : 'danger';
                return '<span class="badge bg-light-' . $color . '">' . ucfirst($row->status) . '</span>';
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ? $row->created_at->format('d M Y H:i') : '-';
            })
            ->rawColumns(['action', 'status']) // Kolom yang mengandung HTML
            ->make(true);
    }

    /**
     * Menampilkan form tambah customer baru.
     */
    public function create()
    {
        $statuses = ['active' => 'Active', 'inactive' => 'Inactive']; // Contoh status
        $genders = ['male' => 'Male', 'female' => 'Female', 'other' => 'Other']; // Contoh gender
        return view('admin.customers.create', compact('statuses', 'genders'));
    }

    /**
     * Menyimpan customer baru ke database.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('customers', 'email')],
            'phone_number' => ['required', 'string', 'max:20'], // Sesuaikan validasi nomor HP jika perlu
            'password' => ['required', Password::min(8)->mixedCase()->numbers(), 'confirmed'], // Password kuat & konfirmasi
            'address' => ['required', 'string'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ]);

        try {
            Customer::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'phone_number' => $validatedData['phone_number'],
                'password' => Hash::make($validatedData['password']), // Hash password
                'address' => $validatedData['address'],
                'gender' => $validatedData['gender'],
                'status' => $validatedData['status'],
            ]);
            return redirect()->route('admin.customers.index')
                ->with('success', 'Customer berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Customer Creation Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat menyimpan customer.');
        }
    }

    /**
     * Menampilkan form edit customer.
     * Menggunakan Route Model Binding dengan Hashid.
     */
    public function edit(Customer $customer)
    {
        $statuses = ['active' => 'Active', 'inactive' => 'Inactive'];
        $genders = ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'];
        return view('admin.customers.edit', compact('customer', 'statuses', 'genders'));
    }

    /**
     * Mengupdate data customer di database.
     */
    public function update(Request $request, Customer $customer)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customer->id)],
            'phone_number' => ['required', 'string', 'max:20'],
            // Password opsional saat update
            'password' => ['nullable', Password::min(8)->mixedCase()->numbers(), 'confirmed'],
            'address' => ['required', 'string'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ]);

        try {
            $updateData = $validatedData;
            // Hanya update password jika diisi
            if (!empty($validatedData['password'])) {
                $updateData['password'] = Hash::make($validatedData['password']);
            } else {
                unset($updateData['password']); // Hapus dari array jika kosong
            }

            $customer->update($updateData);
            return redirect()->route('admin.customers.index')
                ->with('success', 'Data customer berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Customer Update Error: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui data customer.');
        }
    }

    /**
     * Menghapus customer dari database.
     */
    public function destroy(Customer $customer)
    {
        // PENTING: Cek apakah customer memiliki booking?
        if ($customer->bookings()->exists()) {
            return response()->json(['error' => 'Customer tidak dapat dihapus karena memiliki data booking terkait.'], 422);
        }
        // Tambahkan pengecekan relasi lain jika ada (misal: payments)

        try {
            $customerName = $customer->name;
            $customer->delete();
            return response()->json(['message' => "Customer '{$customerName}' berhasil dihapus."]);
        } catch (QueryException $e) {
            Log::error('Customer Deletion Query Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menghapus customer karena terkait dengan data lain.'], 500);
        } catch (\Exception $e) {
            Log::error('Customer Deletion Error: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menghapus customer.'], 500);
        }
    }
}
