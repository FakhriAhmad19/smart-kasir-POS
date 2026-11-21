<x-app-layout>
    <x-slot name="title">Pengaturan Profil Toko & Struk - Smart Kasir</x-slot>
    <x-slot name="header">Pengaturan Profil Toko & Header/Footer Struk</x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card card-glass border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold m-0 text-dark">Informasi Toko & Tampilan Struk Thermal</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('settings.update') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="store_name" class="form-label fw-semibold">Nama Toko / Usaha <span class="text-danger">*</span></label>
                            <input type="text" name="store_name" id="store_name" class="form-control @error('store_name') is-invalid @enderror" value="{{ old('store_name', $settings['store_name']) }}" required>
                            @error('store_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="store_address" class="form-label fw-semibold">Alamat Lengkap Toko <span class="text-danger">*</span></label>
                            <textarea name="store_address" id="store_address" class="form-control @error('store_address') is-invalid @enderror" rows="2" required>{{ old('store_address', $settings['store_address']) }}</textarea>
                            @error('store_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="store_phone" class="form-label fw-semibold">No. Telepon / WhatsApp <span class="text-danger">*</span></label>
                                <input type="text" name="store_phone" id="store_phone" class="form-control @error('store_phone') is-invalid @enderror" value="{{ old('store_phone', $settings['store_phone']) }}" required>
                                @error('store_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="store_email" class="form-label fw-semibold">Email Toko</label>
                                <input type="email" name="store_email" id="store_email" class="form-control @error('store_email') is-invalid @enderror" value="{{ old('store_email', $settings['store_email']) }}">
                                @error('store_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="receipt_footer_note" class="form-label fw-semibold">Pesan / Catatan Footer Struk Kasir</label>
                            <textarea name="receipt_footer_note" id="receipt_footer_note" class="form-control @error('receipt_footer_note') is-invalid @enderror" rows="3" placeholder="Pesan ucapan di bawah struk">{{ old('receipt_footer_note', $settings['receipt_footer_note']) }}</textarea>
                            <small class="text-muted">Pesan ini akan tercetak di bagian bawah setiap struk thermal kasir.</small>
                            @error('receipt_footer_note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">
                        <h6 class="fw-bold text-dark mb-3">Pajak & Service Charge</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="tax_rate" class="form-label fw-semibold">PB1 / Pajak (%)</label>
                                <input type="number" name="tax_rate" id="tax_rate" class="form-control @error('tax_rate') is-invalid @enderror" value="{{ old('tax_rate', $settings['tax_rate']) }}" min="0" max="100" step="0.01" required>
                                @error('tax_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label for="service_charge_rate" class="form-label fw-semibold">Service Charge (%)</label>
                                <input type="number" name="service_charge_rate" id="service_charge_rate" class="form-control @error('service_charge_rate') is-invalid @enderror" value="{{ old('service_charge_rate', $settings['service_charge_rate']) }}" min="0" max="100" step="0.01" required>
                                @error('service_charge_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="form-check mb-4">
                            <input type="hidden" name="prices_include_tax" value="0">
                            <input class="form-check-input" type="checkbox" name="prices_include_tax" value="1" id="prices_include_tax" @checked(old('prices_include_tax', $settings['prices_include_tax']) === '1')>
                            <label class="form-check-label" for="prices_include_tax">Harga menu sudah termasuk pajak</label>
                            <div class="form-text">Jika dicentang, pajak hanya ditampilkan sebagai bagian dari total, bukan ditambahkan lagi.</div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                <i class="bi bi-save me-1"></i> Simpan Perubahan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
