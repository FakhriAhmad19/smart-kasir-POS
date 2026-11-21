{{-- Modifier groups attached to this menu item. Expects $modifierGroups, optional $product. --}}
@php
    $attached = collect(old('modifier_group_ids', isset($product) ? $product->modifierGroups->pluck('id')->all() : []))
        ->map(fn ($id) => (int) $id)->all();
@endphp

<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <label class="form-label fw-semibold small mb-0">Varian & Modifier</label>
        <a href="{{ route('modifiers.create') }}" class="small text-decoration-none" target="_blank">
            <i class="bi bi-plus-lg"></i> Buat grup baru
        </a>
    </div>

    @if($modifierGroups->isEmpty())
        <div class="border rounded-3 p-3 small text-muted bg-light">
            Belum ada grup modifier. Buat dulu di menu <a href="{{ route('modifiers.index') }}">Varian & Modifier</a>
            (misalnya Ukuran, Level Gula, Topping), lalu kembali ke sini.
        </div>
    @else
        <div class="row g-2">
            @foreach($modifierGroups as $group)
                <div class="col-md-6">
                    <label class="d-flex gap-2 border rounded-3 p-2 px-3 h-100 bg-white" style="cursor: pointer;">
                        <input type="checkbox" class="form-check-input mt-1" name="modifier_group_ids[]" value="{{ $group->id }}" {{ in_array($group->id, $attached, true) ? 'checked' : '' }}>
                        <span class="min-w-0">
                            <span class="fw-semibold small d-block">{{ $group->name }}
                                <span class="text-muted fw-normal">· {{ $group->selectionLabel() }}</span>
                            </span>
                            <small class="text-muted d-block text-truncate">{{ $group->options->pluck('name')->join(', ') }}</small>
                        </span>
                    </label>
                </div>
            @endforeach
        </div>
        <small class="text-muted d-block mt-1">Grup yang dicentang akan ditanyakan di kasir saat menu ini dipesan.</small>
    @endif
</div>
