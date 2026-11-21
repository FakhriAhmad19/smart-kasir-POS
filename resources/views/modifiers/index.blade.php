<x-app-layout>
    <x-slot name="title">Varian & Modifier - Smart Kasir</x-slot>
    <x-slot name="header">Varian & Modifier Menu</x-slot>

    <div class="card card-glass border-0 mb-4 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Grup Varian & Modifier</h5>
                    <small class="text-muted">Pilihan yang ditanyakan saat menu dipesan, misalnya ukuran, level gula, jenis susu, atau topping.</small>
                </div>
                <a href="{{ route('modifiers.create') }}" class="btn btn-primary btn-touch shadow-sm fw-bold">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Grup
                </a>
            </div>

            @if($groups->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-sliders display-4 text-secondary opacity-50 d-block mb-2"></i>
                    <p class="mb-1 fw-semibold">Belum ada grup modifier.</p>
                    <small>Contoh: <b>Ukuran</b> (Regular / Large +Rp5.000), <b>Level Gula</b> (Normal / Less / No Sugar), <b>Topping</b> (Boba, Extra Shot).</small>
                </div>
            @else
                <div class="row g-3">
                    @foreach($groups as $group)
                        <div class="col-md-6 col-xl-4">
                            <div class="border rounded-3 p-3 h-100 d-flex flex-column bg-white">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div class="min-w-0">
                                        <h6 class="fw-bold mb-1 text-truncate">{{ $group->name }}</h6>
                                        <span class="badge {{ $group->requiredMin() > 0 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-secondary bg-opacity-10 text-secondary' }}">
                                            {{ $group->selectionLabel() }}
                                        </span>
                                    </div>
                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill text-nowrap">
                                        {{ $group->products_count }} menu
                                    </span>
                                </div>

                                <ul class="list-unstyled small mb-3 flex-grow-1">
                                    @foreach($group->options as $option)
                                        <li class="d-flex justify-content-between py-1 border-bottom {{ $option->is_active ? '' : 'text-muted text-decoration-line-through' }}">
                                            <span>
                                                {{ $option->name }}
                                                @if($option->is_default)
                                                    <span class="badge bg-light text-secondary border ms-1">default</span>
                                                @endif
                                            </span>
                                            <span class="fw-semibold {{ $option->price > 0 ? 'text-primary' : 'text-muted' }}">
                                                {{ $option->price > 0 ? '+Rp' . number_format($option->price, 0, ',', '.') : 'Gratis' }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>

                                <div class="d-flex gap-2">
                                    <a href="{{ route('modifiers.edit', $group) }}" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </a>
                                    <form action="{{ route('modifiers.destroy', $group) }}" method="POST" class="js-confirm-delete d-inline" data-confirm-title="Hapus Grup Modifier?" data-confirm-text="Grup {{ $group->name }} akan dilepas dari semua menu. Riwayat transaksi tidak berubah.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                            <i class="bi bi-trash me-1"></i> Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
