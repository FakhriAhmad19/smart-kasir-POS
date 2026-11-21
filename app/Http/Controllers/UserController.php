<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Roles that can be assigned from the UI.
     */
    private const ASSIGNABLE_ROLES = ['owner', 'admin', 'kasir', 'barista', 'kitchen'];

    public function index()
    {
        $users = User::with('roles')
            ->withCount('transactions')
            ->latest()
            ->paginate(10);

        $roles = Role::whereIn('name', self::ASSIGNABLE_ROLES)->pluck('name');

        return view('users.index', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:app_users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(self::ASSIGNABLE_ROLES)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('app_users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(self::ASSIGNABLE_ROLES)],
        ]);

        // Prevent an owner from stripping their own owner role or deactivating themselves,
        // which could lock them (and possibly the whole store) out of administration.
        $isSelf = $user->id === auth()->id();
        if ($isSelf && $validated['role'] !== 'owner') {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat mengubah peran akun Anda sendiri.');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Owners may not deactivate their own account.
        $user->is_active = $isSelf ? true : $request->boolean('is_active');
        $user->save();

        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Keep transaction history intact: deactivate instead of hard-deleting
        // a cashier/admin that already has recorded transactions.
        if ($user->transactions()->exists()) {
            $user->is_active = false;
            $user->save();

            return redirect()->route('users.index')->with('success', 'Pengguna memiliki riwayat transaksi, akun dinonaktifkan (bukan dihapus permanen).');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
