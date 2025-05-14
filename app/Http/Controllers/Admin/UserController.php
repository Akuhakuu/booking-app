<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // Untuk mendapatkan user yang login (jika perlu untuk validasi)

class UserController extends Controller
{
    /**
     * Menampilkan halaman daftar user (kerangka untuk DataTables).
     */
    public function index()
    {
        return view('admin.users.index');
    }

    /**
     * Menyediakan data users untuk DataTables.
     */
    public function getData(Request $request)
    {
        // Ambil semua user atau filter berdasarkan role jika perlu
        // Misalnya, jika Anda hanya ingin menampilkan admin atau staff
        // $users = User::where('role', 'admin')->orWhere('role', 'staff')->select('users.*');
        $users = User::select('users.*'); // Ambil semua user dari tabel 'users'

        return DataTables::of($users)
            ->addIndexColumn() // Menambahkan kolom DT_RowIndex
            ->addColumn('action', function ($row) {
                // Menggunakan $row->hashid yang di-generate oleh Trait Hashidable
                $editUrl = route('admin.users.edit', ['user' => $row->hashid]);
                $deleteUrl = route('admin.users.destroy', ['user' => $row->hashid]);

                // Tombol delete dengan konfirmasi JavaScript inline (atau bisa pakai SweetAlert)
                // Untuk SweetAlert, onclick akan memanggil fungsi JS seperti di ItemController
                $deleteButton = '
                <form action="' . $deleteUrl . '" method="POST" style="display:inline-block;" onsubmit="return confirm(\'Apakah Anda yakin ingin menghapus user ini?\');">
                    ' . csrf_field() . '
                    ' . method_field('DELETE') . '
                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                </form>';

                return '<a href="' . $editUrl . '" class="btn btn-sm btn-warning">Edit</a> ' . $deleteButton;
            })
            ->editColumn('status', function ($row) {
                if ($row->status === 'active') {
                    return '<span class="badge bg-success">Aktif</span>';
                } elseif ($row->status === 'inactive') {
                    return '<span class="badge bg-danger">Nonaktif</span>';
                }
                return '<span class="badge bg-secondary">' . ucfirst($row->status) . '</span>';
            })
            ->editColumn('gender', fn($row) => ucfirst($row->gender ?? '-'))
            ->editColumn('created_at', fn($row) => $row->created_at?->format('d M Y, H:i') ?? '-')
            ->rawColumns(['action', 'status']) // Kolom yang mengandung HTML
            ->make(true);
    }

    /**
     * Menampilkan form tambah user baru.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Menyimpan user baru ke database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed', // Min 8 & confirmed
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'gender' => 'required|string|in:Laki-laki,Perempuan',
            'status' => 'required|string|in:active,inactive', // Validasi status
        ]);

        try {
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'gender' => $request->gender,
                'status' => $request->status,
                'email_verified_at' => now(), // Langsung verifikasi jika dibuat admin
            ]);
            return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('User Creation Error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan user. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan form edit user.
     * Laravel akan otomatis resolve User dari {user:hashid}
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Mengupdate data user di database.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed', // Password opsional, tapi jika diisi harus min 8 dan confirmed
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'gender' => 'nullable|string|in:Laki-laki,Perempuan',
            'status' => 'required|string|in:active,inactive',
        ]);

        try {
            $dataToUpdate = [
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'gender' => $request->gender,
                'status' => $request->status,
            ];

            if ($request->filled('password')) {
                $dataToUpdate['password'] = Hash::make($request->password);
            }

            $user->update($dataToUpdate);
            return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('User Update Error: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui user. Silakan coba lagi.');
        }
    }

    /**
     * Menghapus user dari database.
     */
    public function destroy(User $user)
    {
        try {
            // Opsional: Tambahkan validasi agar tidak bisa menghapus diri sendiri
            if (Auth::id() === $user->id) {
                 return redirect()->route('admin.users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            }

            $userName = $user->name;
            $user->delete();
            // Menggunakan redirect untuk halaman non-AJAX. Jika AJAX, return JSON.
            return redirect()->route('admin.users.index')->with('success', "User '{$userName}' berhasil dihapus.");
        } catch (\Exception $e) {
            Log::error('User Deletion Error: ' . $e->getMessage());
            return redirect()->route('admin.users.index')->with('error', 'Gagal menghapus user.');
        }
    }
}
