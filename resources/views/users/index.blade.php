<x-app-layout>
    <x-slot name="title">Manajemen Pengguna - Smart Kasir</x-slot>
    <x-slot name="header">Manajemen Pengguna</x-slot>

    @php
        $roleBadges = [
            'owner' => 'bg-primary bg-opacity-10 text-primary',
            'admin' => 'bg-info bg-opacity-10 text-info',
            'kasir' => 'bg-secondary bg-opacity-10 text-secondary',
        ];
        $roleLabels = ['owner' => 'Owner', 'admin' => 'Admin', 'kasir' => 'Kasir'];
    @endphp

    <div class="card card-glass border-0 mb-4 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Daftar Pengguna Sistem</h5>
                    <small class="text-muted">Kelola akun owner, admin, dan kasir beserta hak akses & statusnya</small>
                </div>
                <button type="button" class="btn btn-primary btn-touch px-3.5 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#modalAddUser">
                    <i class="bi bi-person-plus-fill me-1"></i> Tambah Pengguna
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase fw-bold text-secondary" style="font-size: 0.75rem;">
                        <tr>
                            <th class="ps-4" width="5%">No</th>
                            <th width="25%">Nama</th>
                            <th width="25%">Email</th>
                            <th class="text-center" width="12%">Peran</th>
                            <th class="text-center" width="12%">Status</th>
                            <th class="text-end pe-4" width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                            @php $userRole = $user->roles->first()->name ?? null; @endphp
                            <tr>
                                <td class="ps-4 text-muted font-monospace">{{ $users->firstItem() + $index }}</td>
                                <td>
                                    <div class="fw-bold text-dark fs-6 text-ellipsis">
                                        {{ $user->name }}
                                        @if($user->id === auth()->id())
                                            <span class="badge bg-dark bg-opacity-10 text-dark ms-1 rounded-pill" style="font-size: 0.6rem;">Anda</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-muted small text-ellipsis">{{ $user->email }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $roleBadges[$userRole] ?? 'bg-light text-muted' }} px-3 py-1.5 rounded-pill fw-bold text-capitalize">
                                        {{ $roleLabels[$userRole] ?? ($userRole ?? 'Tanpa Peran') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($user->is_active)
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-1.5 rounded-pill fw-bold">
                                            <i class="bi bi-check-circle-fill me-1"></i>Aktif
                                        </span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-1.5 rounded-pill fw-bold">
                                            <i class="bi bi-slash-circle-fill me-1"></i>Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalEditUser{{ $user->id }}" title="Edit Pengguna">
                                            <i class="bi bi-pencil me-1"></i> Edit
                                        </button>
                                        @if($user->id !== auth()->id())
                                        <form action="{{ route('users.destroy', $user) }}" method="POST" class="js-confirm-delete d-inline" data-confirm-title="Hapus Pengguna?" data-confirm-text="Yakin ingin menghapus pengguna {{ $user->name }}? Jika sudah memiliki transaksi, akun akan dinonaktifkan.">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3" title="Hapus Pengguna">
                                                <i class="bi bi-trash me-1"></i> Hapus
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>

                            <!-- Modal Edit -->
                            <div class="modal fade" id="modalEditUser{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold">Edit Pengguna</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('users.update', $user) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body p-4">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                                                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Peran <span class="text-danger">*</span></label>
                                                    <select name="role" class="form-select" {{ $user->id === auth()->id() ? 'disabled' : '' }} required>
                                                        @foreach($roles as $role)
                                                            <option value="{{ $role }}" {{ old('role', $userRole) === $role ? 'selected' : '' }}>{{ $roleLabels[$role] ?? ucfirst($role) }}</option>
                                                        @endforeach
                                                    </select>
                                                    @if($user->id === auth()->id())
                                                        <input type="hidden" name="role" value="{{ $userRole }}">
                                                        <small class="text-muted">Anda tidak dapat mengubah peran akun sendiri.</small>
                                                    @endif
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Kata Sandi Baru</label>
                                                    <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diubah">
                                                    <small class="text-muted">Minimal 8 karakter. Isi hanya bila ingin reset sandi.</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Ulangi Kata Sandi Baru</label>
                                                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi sandi baru">
                                                </div>
                                                @if($user->id !== auth()->id())
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="is_active" value="0">
                                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editActive{{ $user->id }}" {{ $user->is_active ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-semibold" for="editActive{{ $user->id }}">Akun Aktif (dapat login)</label>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer border-0 pt-0 pe-4 pb-4">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-people display-4 text-secondary opacity-50 d-block mb-2"></i>
                                    Belum ada pengguna. Silakan tambah pengguna baru.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Modal Add User -->
    <div class="modal fade" id="modalAddUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Tambah Pengguna Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Siti (Kasir)" value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="nama@smartkasir.com" value="{{ old('email') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Peran <span class="text-danger">*</span></label>
                            <select name="role" class="form-select" required>
                                <option value="" disabled {{ old('role') ? '' : 'selected' }}>Pilih peran...</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role }}" {{ old('role') === $role ? 'selected' : '' }}>{{ $roleLabels[$role] ?? ucfirst($role) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Kata Sandi <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="Minimal 8 karakter" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Ulangi Kata Sandi <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi kata sandi" required>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="addActive" {{ old('is_active', '1') ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="addActive">Akun Aktif (dapat login)</label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pe-4 pb-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Pengguna</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: `{!! implode('<br>', $errors->all()) !!}`,
                    confirmButtonColor: '#1d4ed8',
                    confirmButtonText: 'OK',
                });
            });
        </script>
    @endif
</x-app-layout>
