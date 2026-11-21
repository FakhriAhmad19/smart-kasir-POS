{{-- Product photo upload with live preview. Expects optional $product. --}}
@php
    $currentUrl = isset($product) ? $product->image_url : null;
@endphp

<div class="mb-4">
    <label class="form-label fw-semibold small">Foto Produk (Opsional)</label>
    <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-3">
        <div class="product-image-preview" id="imagePreviewBox">
            <img @if($currentUrl) src="{{ $currentUrl }}" @endif alt="Preview foto produk" id="imagePreview" class="{{ $currentUrl ? '' : 'd-none' }}">
            <div class="product-image-placeholder {{ $currentUrl ? 'd-none' : '' }}" id="imagePlaceholder">
                <i class="bi bi-image fs-2"></i>
                <small>Belum ada foto</small>
            </div>
        </div>
        <div class="flex-grow-1">
            <input type="file" name="image" id="imageInput" class="form-control @error('image') is-invalid @enderror" accept="image/jpeg,image/png,image/webp">
            @error('image')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="text-muted d-block mt-1">Format JPG, PNG, atau WEBP. Maksimal 2 MB. Disarankan rasio persegi (1:1).</small>
            @if($currentUrl)
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="removeImage">
                    <label class="form-check-label small text-danger" for="removeImage">Hapus foto saat ini</label>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        const input = document.getElementById('imageInput');
        const img = document.getElementById('imagePreview');
        const placeholder = document.getElementById('imagePlaceholder');
        const removeCheck = document.getElementById('removeImage');
        const originalSrc = img.getAttribute('src') || '';

        function show(src) {
            if (src) {
                img.src = src;
                img.classList.remove('d-none');
                placeholder.classList.add('d-none');
            } else {
                img.removeAttribute('src');
                img.classList.add('d-none');
                placeholder.classList.remove('d-none');
            }
        }

        input.addEventListener('change', function () {
            const file = this.files && this.files[0];
            if (!file) { show(removeCheck && removeCheck.checked ? '' : originalSrc); return; }
            if (file.size > 2 * 1024 * 1024) {
                alert('Ukuran foto melebihi 2 MB.');
                this.value = '';
                show(originalSrc);
                return;
            }
            if (removeCheck) removeCheck.checked = false;
            show(URL.createObjectURL(file));
        });

        if (removeCheck) {
            removeCheck.addEventListener('change', function () {
                if (this.checked) { input.value = ''; show(''); }
                else { show(originalSrc); }
            });
        }
    })();
</script>
@endpush
