<x-pos-layout>
    <!-- Left Column: Product Search & Selection -->
    <div class="flex-grow-1 d-flex flex-column bg-light h-100 overflow-hidden" style="min-width: 0;">
        <!-- Top POS Header & Search Bar -->
        <div class="bg-white border-bottom p-3">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3 flex-wrap">
                <div class="d-flex align-items-center gap-2 min-w-0">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-touch px-2 flex-shrink-0" title="Kembali ke Dashboard">
                        <i class="bi bi-arrow-left fs-5"></i>
                    </a>
                    <h4 class="fw-bold m-0 text-dark text-truncate">Kasir / POS</h4>
                </div>
                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <button type="button" class="btn btn-sm btn-warning rounded-pill px-3 py-1.5 fw-semibold text-nowrap position-relative" id="btnOpenBills" title="Bill terbuka (bayar nanti)">
                        <i class="bi bi-receipt"></i> <span class="d-none d-md-inline ms-1">Bill Terbuka</span>
                        <span class="badge rounded-pill bg-danger ms-1" id="openBillCount">0</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold text-nowrap" id="btnConnectBluetoothPrinter" onclick="connectBluetoothPrinter()" title="Hubungkan Printer Bluetooth">
                        <i class="bi bi-printer"></i> <span id="bluetoothStatusText" class="d-none d-lg-inline ms-1">Hubungkan Printer Bluetooth</span>
                    </button>
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill text-nowrap">
                        <i class="bi bi-wifi"></i> <span class="d-none d-sm-inline">Kasir Online</span>
                    </span>
                    <span class="text-muted small fw-semibold d-none d-md-inline text-truncate" style="max-width: 160px;">
                        <i class="bi bi-person-circle me-1"></i> {{ auth()->user()->name }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 text-nowrap" title="Keluar / Logout">
                            <i class="bi bi-box-arrow-right"></i> <span class="d-none d-sm-inline ms-1">Keluar</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Search Inputs -->
            <div class="row g-2">
                <div class="col-md-7">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted fs-5"></i></span>
                        <input type="text" id="searchInput" class="form-control bg-light border-start-0 fs-6" placeholder="Cari nama, SKU, atau scan barcode..." autofocus autocomplete="off">
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-barcode text-primary fs-5"></i></span>
                        <input type="text" id="barcodeScannerInput" class="form-control bg-light border-start-0 fs-6" placeholder="Scanner Barcode Standby..." autocomplete="off">
                    </div>
                </div>
            </div>

            <!-- Category Pills -->
            <div class="d-flex gap-2 mt-3 overflow-x-auto pb-1" id="categoryPills">
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold category-pill active" data-id="">
                    Semua Produk
                </button>
                @foreach($categories as $category)
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold category-pill" data-id="{{ $category->id }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Product Grid Area -->
        <div class="p-3 flex-grow-1 overflow-y-auto">
            <div class="row g-3" id="productGrid">
                @foreach($products as $product)
                    <div class="col-6 col-sm-4 col-md-3 col-xl-2.4 product-item" 
                         data-id="{{ $product->id }}"
                         data-sku="{{ $product->sku }}"
                         data-barcode="{{ $product->barcode }}"
                         data-name="{{ $product->name }}"
                         data-price="{{ $product->selling_price }}"
                         data-stock="{{ $product->stock }}"
                         data-min-stock="{{ $product->min_stock }}"
                         data-image="{{ $product->image_url }}"
                         data-category="{{ $product->app_category_id }}">
                        <div class="pos-product-card h-100 d-flex flex-column">
                            <div class="pos-product-thumb">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
                                @else
                                    <div class="pos-product-thumb-empty"><i class="bi bi-box-seam"></i></div>
                                @endif
                                <span class="badge bg-white text-secondary border small pos-thumb-category">{{ $product->category->name ?? 'Umum' }}</span>
                                <span class="badge {{ $product->stock <= $product->min_stock ? 'bg-danger' : 'bg-success' }} rounded-pill pos-thumb-stock" id="stockBadge-{{ $product->id }}">
                                    Stok: {{ $product->stock }}
                                </span>
                            </div>
                            <div class="px-3 pb-3 pt-2 d-flex flex-column justify-content-between flex-grow-1">
                            <div>
                                <h6 class="fw-bold text-dark mb-1 text-truncate-2" style="font-size: 0.925rem; min-height: 2.6rem;">{{ $product->name }}</h6>
                                @if($product->modifierGroups->isNotEmpty())
                                    <small class="text-primary d-block text-truncate mb-2" style="font-size: 0.75rem;"><i class="bi bi-sliders me-1"></i>{{ $product->modifierGroups->pluck('name')->join(', ') }}</small>
                                @else
                                    <small class="text-muted d-block text-truncate mb-2" style="font-size: 0.75rem;">SKU: {{ $product->sku }}</small>
                                @endif
                            </div>
                            <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary" style="font-size: 1rem;">Rp{{ number_format($product->selling_price, 0, ',', '.') }}</span>
                                <button type="button" class="btn btn-sm btn-primary btn-touch rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Right Column: Shopping Cart & Checkout Panel -->
    <div class="pos-cart-panel shadow-lg" style="width: 420px; min-width: 420px;">
        <!-- Cart Header -->
        <div class="p-3 border-bottom bg-white d-flex align-items-center justify-content-between">
            <div>
                <h5 class="fw-bold mb-0 text-dark">Keranjang Belanja</h5>
                <small class="text-muted" id="cartItemCount">0 item dipilih</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger" id="btnClearCart" title="Kosongkan Keranjang">
                <i class="bi bi-trash me-1"></i> Bersihkan
            </button>
        </div>

        <!-- Order Info: type, table, customer -->
        <div class="px-3 pt-3 pb-2 border-bottom bg-white" id="orderInfoPanel">
            <div class="btn-group w-100 mb-2" role="group" aria-label="Tipe pesanan">
                <input type="radio" class="btn-check" name="order_type" id="otDineIn" value="dine_in" checked>
                <label class="btn btn-outline-primary btn-sm py-2" for="otDineIn"><i class="bi bi-cup-hot me-1"></i>Dine-in</label>
                <input type="radio" class="btn-check" name="order_type" id="otTakeAway" value="take_away">
                <label class="btn btn-outline-primary btn-sm py-2" for="otTakeAway"><i class="bi bi-bag me-1"></i>Take Away</label>
                <input type="radio" class="btn-check" name="order_type" id="otOnline" value="online">
                <label class="btn btn-outline-primary btn-sm py-2" for="otOnline"><i class="bi bi-phone me-1"></i>Online</label>
            </div>
            <div class="row g-2">
                <div class="col-6" id="tableWrap">
                    <select id="tableSelect" class="form-select form-select-sm" aria-label="Meja">
                        @if($tables->isEmpty())
                            <option value="">Belum ada meja</option>
                        @else
                            <option value="">-- Pilih Meja --</option>
                            @foreach($tables->groupBy(fn ($t) => $t->area ?: 'Lainnya') as $area => $areaTables)
                                <optgroup label="{{ $area }}">
                                    @foreach($areaTables as $table)
                                        <option value="{{ $table->id }}">{{ $table->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-6 d-none" id="platformWrap">
                    <select id="platformSelect" class="form-select form-select-sm" aria-label="Platform online">
                        <option value="">-- Platform --</option>
                        @foreach(\App\Models\Transaction::ONLINE_PLATFORMS as $platform)
                            <option value="{{ $platform }}">{{ $platform }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6" id="customerWrap">
                    <input type="text" id="customerName" class="form-control form-control-sm" maxlength="100" placeholder="Nama pelanggan (opsional)">
                </div>
            </div>
            @if($tables->isEmpty())
                <small class="text-danger d-block mt-1" id="noTableHint">
                    <i class="bi bi-exclamation-circle"></i> Dine-in butuh meja. Tambahkan di menu <b>Meja</b>.
                </small>
            @endif
        </div>

        <!-- Cart Item List -->
        <div class="pos-cart-items p-3" id="cartContainer">
            <div class="text-center py-5 text-muted" id="cartEmptyState">
                <i class="bi bi-cart-x display-3 text-secondary opacity-50 mb-2 d-block"></i>
                <p class="fw-semibold mb-1">Keranjang masih kosong</p>
                <small>Klik produk di sebelah kiri atau scan barcode untuk menambahkan.</small>
            </div>
            <div class="d-flex flex-column gap-2" id="cartList"></div>
        </div>

        <!-- Cart Footer & Calculation Panel -->
        <div class="p-3 border-top bg-white mt-auto">
            <!-- Shown while adding items to an existing open bill -->
            <div class="alert alert-warning py-2 px-3 mb-3 d-none" id="activeBillBanner">
                <div class="d-flex justify-content-between align-items-center gap-2">
                    <div class="min-w-0 small">
                        <div class="fw-bold text-truncate"><i class="bi bi-plus-circle me-1"></i>Tambah pesanan ke <span id="activeBillLabel">-</span></div>
                        <div class="text-muted">Pilih menu lalu tekan <b>Kirim ke Bill</b>.</div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-secondary flex-shrink-0" id="btnExitBillMode">Batal</button>
                </div>
                <button type="button" class="btn btn-warning btn-touch w-100 fw-bold mt-2" id="btnSendToBill" disabled>
                    <i class="bi bi-send me-1"></i> Kirim ke Bill
                </button>
            </div>

            <!-- Calculation Breakdown -->
            <div class="mb-3">
                <div class="d-flex justify-content-between text-muted small mb-1">
                    <span>Subtotal:</span>
                    <span class="fw-bold text-dark" id="displaySubtotal">Rp0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Diskon (Rp):</span>
                    <input type="number" id="discountInput" class="form-control form-control-sm text-end" style="width: 120px;" placeholder="0" min="0" value="0">
                </div>
                <div class="d-flex justify-content-between text-muted small mb-1">
                    <span>Service charge:</span>
                    <span id="displayServiceCharge">Rp0</span>
                </div>
                <div class="d-flex justify-content-between text-muted small mb-2">
                    <span>Pajak:</span>
                    <span id="displayTax">Rp0</span>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <h5 class="fw-bold text-dark m-0">TOTAL:</h5>
                    <h3 class="fw-extrabold text-primary m-0" id="displayFinalTotal">Rp0</h3>
                </div>
            </div>

            <div id="payNowSection">
            <button type="button" class="btn btn-outline-warning text-dark btn-touch w-100 fw-bold mb-3" id="btnSaveBill" disabled>
                <i class="bi bi-clock-history me-1"></i> Bayar Nanti (Simpan sebagai Bill)
            </button>

            <!-- Payment Method Tabs -->
            <ul class="nav nav-pills nav-justified mb-3 gap-2" id="paymentTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold btn-touch py-2" id="cash-tab" data-bs-toggle="tab" data-bs-target="#cash-panel" type="button" role="tab">
                        <i class="bi bi-cash-stack me-1"></i> Tunai (Cash)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold btn-touch py-2" id="qris-tab" data-bs-toggle="tab" data-bs-target="#qris-panel" type="button" role="tab">
                        <i class="bi bi-qr-code-scan me-1"></i> QRIS UMKM
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="paymentTabContent">
                <!-- Cash Payment Panel -->
                <div class="tab-pane fade show active" id="cash-panel" role="tabpanel">
                    <div class="mb-2">
                        <label class="form-label fw-semibold small text-secondary mb-1">Nominal Uang Bayar (Rp)</label>
                        <input type="number" id="cashPaidInput" class="form-control form-control-lg fw-bold text-end" placeholder="0">
                    </div>

                    <!-- Quick Cash Buttons -->
                    <div class="row g-1.5 mb-2">
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-secondary w-100 btn-quick-cash" id="btnExactCash">Uang Pas</button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-secondary w-100 btn-quick-cash" onclick="addQuickCash(10000)">10.000</button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-secondary w-100 btn-quick-cash" onclick="addQuickCash(20000)">20.000</button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-secondary w-100 btn-quick-cash" onclick="addQuickCash(50000)">50.000</button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-secondary w-100 btn-quick-cash" onclick="addQuickCash(100000)">100.000</button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-danger w-100 btn-quick-cash" onclick="clearCashInput()"><i class="bi bi-backspace"></i></button>
                        </div>
                    </div>

                    <!-- Change Calculation Indicator -->
                    <div class="p-2.5 rounded-3 bg-light border d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-semibold text-secondary small">Kembalian:</span>
                        <span class="fw-bold fs-5 text-success" id="displayChange">Rp0</span>
                    </div>

                    <button type="button" class="btn btn-success btn-touch w-100 py-3 fw-bold fs-5 shadow" id="btnCheckoutCash" disabled>
                        <i class="bi bi-check-circle-fill me-2"></i> Bayar Tunai
                    </button>
                </div>

                <!-- QRIS Payment Panel -->
                <div class="tab-pane fade" id="qris-panel" role="tabpanel">
                    <div class="p-3 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25 text-center mb-3">
                        <i class="bi bi-qr-code-scan text-primary display-5 d-block mb-2"></i>
                        <h6 class="fw-bold text-dark mb-1">Pembayaran Non-Tunai QRIS</h6>
                        <small class="text-muted d-block">Scan kode QR yang tampil dan konfirmasi dana masuk merchant.</small>
                    </div>

                    <button type="button" class="btn btn-primary btn-touch w-100 py-3 fw-bold fs-5 shadow" id="btnOpenQrisModal">
                        <i class="bi bi-qr-code me-2"></i> Tampilkan Kode QRIS
                    </button>
                </div>
            </div>
            </div><!-- /#payNowSection -->
        </div>
    </div>

    <!-- Modal: Open Bills list -->
    <div class="modal fade" id="billsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-receipt me-2"></i>Bill Terbuka</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body pt-0">
                    <div id="billsList" class="row g-2"></div>
                    <div id="billsEmpty" class="text-center text-muted py-5 d-none">
                        <i class="bi bi-receipt-cutoff display-4 opacity-50 d-block mb-2"></i>
                        Tidak ada bill terbuka.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Open Bill detail & payment -->
    <div class="modal fade" id="billModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-0 pb-1 align-items-start">
                    <div class="min-w-0">
                        <h5 class="modal-title fw-bold text-truncate" id="billTitle">-</h5>
                        <small class="text-muted" id="billSubtitle">-</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnBillAddItems"><i class="bi bi-plus-lg me-1"></i>Tambah Pesanan</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnBillPrint"><i class="bi bi-printer me-1"></i>Cetak Bill</button>
                        <button type="button" class="btn btn-sm btn-outline-danger ms-auto" id="btnBillCancel"><i class="bi bi-x-circle me-1"></i>Batalkan Bill</button>
                    </div>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="billSplitToggle">
                        <label class="form-check-label small fw-semibold" for="billSplitToggle">Bayar sebagian item (split bill)</label>
                    </div>
                    <div class="border rounded-3 mb-3" id="billItems"></div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between small text-muted mb-1"><span>Subtotal dibayar</span><span class="fw-bold text-dark" id="billSubtotal">Rp0</span></div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small text-muted">Diskon (Rp)</span>
                                <input type="number" id="billDiscount" class="form-control form-control-sm text-end" style="width: 120px;" min="0" value="0">
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="fw-bold">TOTAL</span>
                                <span class="fw-extrabold text-primary fs-4" id="billTotal">Rp0</span>
                            </div>
                            <div class="small text-muted mt-1 d-none" id="billRemaining"></div>
                        </div>
                        <div class="col-md-6">
                            <div class="btn-group w-100 mb-2" role="group">
                                <input type="radio" class="btn-check" name="bill_method" id="billCash" value="cash" checked>
                                <label class="btn btn-outline-primary btn-sm" for="billCash"><i class="bi bi-cash-stack me-1"></i>Tunai</label>
                                <input type="radio" class="btn-check" name="bill_method" id="billQris" value="qris">
                                <label class="btn btn-outline-primary btn-sm" for="billQris"><i class="bi bi-qr-code me-1"></i>QRIS</label>
                            </div>
                            <div id="billCashWrap">
                                <div class="input-group input-group-sm mb-1">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" id="billCashPaid" class="form-control fw-bold text-end" placeholder="Uang diterima">
                                    <button type="button" class="btn btn-outline-secondary" id="billExact">Uang Pas</button>
                                </div>
                                <div class="d-flex justify-content-between small"><span class="text-muted">Kembalian</span><span class="fw-bold" id="billChange">Rp0</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-success btn-touch w-100 fw-bold fs-5" id="btnBillPay">
                        <i class="bi bi-check-circle-fill me-1"></i> Bayar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Modifier / Variant Selection -->
    <div class="modal fade" id="modifierModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-0 pb-2 align-items-start">
                    <div class="d-flex gap-3 align-items-center min-w-0">
                        <div class="pos-cart-thumb" style="width:56px;height:56px;min-width:56px;" id="modProductThumb"></div>
                        <div class="min-w-0">
                            <h5 class="modal-title fw-bold text-truncate" id="modProductName">-</h5>
                            <small class="text-muted">Harga dasar <span id="modProductBase">Rp0</span></small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body pt-2">
                    <div id="modGroups"></div>
                    <label class="form-label small mt-1" for="modNotes">Catatan (opsional)</label>
                    <input type="text" id="modNotes" class="form-control" maxlength="255" placeholder="Contoh: es dipisah, jangan terlalu manis">
                </div>
                <div class="modal-footer border-0 pt-0 flex-nowrap gap-2">
                    <div class="d-flex align-items-center gap-1 border rounded-2 p-1 flex-shrink-0">
                        <button type="button" class="btn btn-sm btn-light border-0 fw-bold px-3" id="modQtyMinus">-</button>
                        <span class="fw-bold px-2" id="modQty">1</span>
                        <button type="button" class="btn btn-sm btn-light border-0 fw-bold px-3" id="modQtyPlus">+</button>
                    </div>
                    <button type="button" class="btn btn-primary btn-touch flex-grow-1 fw-bold" id="modAddBtn">
                        Tambah · <span id="modTotal">Rp0</span>
                    </button>
                </div>
                <div class="px-3 pb-3 small text-danger text-end d-none" id="modHint"></div>
            </div>
        </div>
    </div>

    <!-- Modal QRIS Payment -->
    <div class="modal fade" id="qrisModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 text-center p-4">
                <div class="modal-header border-0 pb-0 justify-content-end">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2">
                    <h4 class="fw-bold mb-1">QRIS Merchant UMKM</h4>
                    <p class="text-muted small mb-3">Pindai menggunakan M-Banking / E-Wallet (Gopay, OVO, Dana, ShopeePay)</p>
                    
                    <!-- Dynamic/Static QR Code Image Placeholder -->
                    <div class="bg-white p-3 d-inline-block rounded-3 border shadow-sm mb-3">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data=SMART-KASIR-UMKM-QRIS-NOMINAL" alt="QRIS Code" id="qrisImage" class="img-fluid">
                    </div>

                    <div class="bg-light p-3 rounded-3 mb-4">
                        <small class="text-muted d-block uppercase fw-semibold">Nominal Pas yang Wajib Ditransfer:</small>
                        <h2 class="fw-extrabold text-primary m-0" id="qrisModalTotal">Rp0</h2>
                    </div>

                    <button type="button" class="btn btn-success btn-touch w-100 py-3 fw-bold fs-5 shadow" id="btnCheckoutQris">
                        <i class="bi bi-check-circle-fill me-2"></i> Konfirmasi Pembayaran Dana Masuk
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Receipt & Print -->
    <div class="modal fade" id="receiptModal" tabindex="-1" data-bs-backdrop="static" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-success"><i class="bi bi-check-circle-fill me-2"></i> Transaksi Berhasil!</h5>
                </div>
                <div class="modal-body text-center p-4">
                    <!-- Ukuran Kertas Selector -->
                    <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded-3 mb-3">
                        <small class="fw-semibold text-secondary ms-2"><i class="bi bi-sliders me-1"></i> Ukuran Kertas:</small>
                        <div class="btn-group btn-group-sm" role="group">
                            <input type="radio" class="btn-check" name="paper_size" id="paper58" value="58mm" checked onchange="setPaperSize('58mm')">
                            <label class="btn btn-outline-primary" for="paper58">58mm (Standar)</label>
                            <input type="radio" class="btn-check" name="paper_size" id="paper80" value="80mm" onchange="setPaperSize('80mm')">
                            <label class="btn btn-outline-primary" for="paper80">80mm (Lebar)</label>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded-3 mb-4 text-start overflow-auto" style="max-height: 350px;" id="receiptContainer"></div>
                    
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary w-50 btn-touch" data-bs-dismiss="modal">
                            Transaksi Baru
                        </button>
                        <button type="button" class="btn btn-primary w-50 btn-touch fw-bold" onclick="printReceipt()">
                            <i class="bi bi-printer-fill me-2"></i> Cetak Struk
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Client-Side POS Logic Javascript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function notify(icon, message) {
                Swal.fire({
                    icon: icon,
                    title: icon === 'success' ? 'Berhasil' : (icon === 'warning' ? 'Perhatian' : 'Gagal'),
                    text: message,
                    confirmButtonColor: '#1d4ed8',
                    confirmButtonText: 'OK',
                });
            }

            let cart = [];
            let activePaymentMethod = 'cash';
            let discountAmount = 0;
            const taxRate = @json((float) \App\Models\Setting::get('tax_rate', '0'));
            const serviceChargeRate = @json((float) \App\Models\Setting::get('service_charge_rate', '0'));
            const pricesIncludeTax = @json(\App\Models\Setting::get('prices_include_tax', '0') === '1');

            const cartContainer = document.getElementById('cartContainer');
            const cartEmptyState = document.getElementById('cartEmptyState');
            const cartList = document.getElementById('cartList');
            const cartItemCount = document.getElementById('cartItemCount');
            const displaySubtotal = document.getElementById('displaySubtotal');
            const displayFinalTotal = document.getElementById('displayFinalTotal');
            const displayServiceCharge = document.getElementById('displayServiceCharge');
            const displayTax = document.getElementById('displayTax');
            const discountInput = document.getElementById('discountInput');
            const cashPaidInput = document.getElementById('cashPaidInput');
            const displayChange = document.getElementById('displayChange');
            const btnCheckoutCash = document.getElementById('btnCheckoutCash');
            const btnExactCash = document.getElementById('btnExactCash');
            const searchInput = document.getElementById('searchInput');
            const barcodeScannerInput = document.getElementById('barcodeScannerInput');

            // Category filter click handler
            document.querySelectorAll('.category-pill').forEach(pill => {
                pill.addEventListener('click', function() {
                    document.querySelectorAll('.category-pill').forEach(p => p.classList.remove('active', 'btn-primary'));
                    document.querySelectorAll('.category-pill').forEach(p => p.classList.add('btn-outline-secondary'));
                    this.classList.remove('btn-outline-secondary');
                    this.classList.add('active', 'btn-primary');
                    
                    const catId = this.dataset.id;
                    filterProducts(searchInput.value, catId);
                });
            });

            // Product Cards click event
            document.querySelectorAll('.product-item').forEach(item => {
                item.addEventListener('click', function() {
                    selectProduct(this);
                });
            });

            // Search product handler
            searchInput.addEventListener('input', function() {
                const activeCat = document.querySelector('.category-pill.active')?.dataset.id || '';
                filterProducts(this.value, activeCat);
            });

            // Barcode Scanner Listener
            barcodeScannerInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const barcode = this.value.trim();
                    if (barcode) {
                        const productEl = Array.from(document.querySelectorAll('.product-item')).find(el => el.dataset.barcode === barcode || el.dataset.sku === barcode);
                        if (productEl) {
                            selectProduct(productEl);
                            this.value = '';
                        } else {
                            notify('warning', 'Produk dengan barcode "' + barcode + '" tidak ditemukan.');
                            this.value = '';
                        }
                    }
                }
            });

            function filterProducts(query, catId) {
                const q = query.toLowerCase();
                document.querySelectorAll('.product-item').forEach(item => {
                    const name = item.dataset.name.toLowerCase();
                    const sku = item.dataset.sku.toLowerCase();
                    const barcode = (item.dataset.barcode || '').toLowerCase();
                    const itemCat = item.dataset.category;

                    const matchesQuery = name.includes(q) || sku.includes(q) || barcode.includes(q);
                    const matchesCategory = !catId || itemCat === catId;

                    if (matchesQuery && matchesCategory) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }

            // Modifier groups per product id (only products that have any).
            const productModifiers = @json($productModifiers);

            function productFromEl(el) {
                return {
                    id: parseInt(el.dataset.id),
                    name: el.dataset.name,
                    image: el.dataset.image || '',
                    price: parseFloat(el.dataset.price),
                    stock: parseInt(el.dataset.stock)
                };
            }

            function selectProduct(el) {
                const product = productFromEl(el);
                if (product.stock <= 0) {
                    notify('warning', 'Stok produk ' + product.name + ' telah habis!');
                    return;
                }
                if ((productModifiers[product.id] || []).length) {
                    openModifierModal(product);
                } else {
                    addLine(product, { optionIds: [], labels: [], total: 0 }, '', 1);
                }
            }

            // Total quantity of one product across all cart lines (lines differ
            // by modifiers/notes but draw from the same stock).
            function qtyInCart(productId) {
                return cart.filter(i => i.id === productId).reduce((sum, i) => sum + i.quantity, 0);
            }

            function addLine(product, mods, notes, qty) {
                if (qtyInCart(product.id) + qty > product.stock) {
                    notify('warning', 'Jumlah keranjang melebihi stok yang tersedia (' + product.stock + ')');
                    return false;
                }

                const optionIds = [...mods.optionIds].sort((a, b) => a - b);
                const key = product.id + '|' + optionIds.join(',') + '|' + notes;
                const existing = cart.find(i => i.key === key);

                if (existing) {
                    existing.quantity += qty;
                } else {
                    cart.push({
                        key: key,
                        id: product.id,
                        name: product.name,
                        image: product.image || '',
                        price: product.price + mods.total,
                        modifier_option_ids: optionIds,
                        modifier_labels: mods.labels,
                        notes: notes,
                        stock: product.stock,
                        quantity: qty
                    });
                }

                renderCart();
                return true;
            }

            window.updateCartQty = function(index, delta) {
                const item = cart[index];
                if (item) {
                    const newQty = item.quantity + delta;
                    if (newQty <= 0) {
                        cart.splice(index, 1);
                    } else if (delta > 0 && qtyInCart(item.id) + delta > item.stock) {
                        notify('warning', 'Stok tidak mencukupi. Maksimal: ' + item.stock);
                    } else {
                        item.quantity = newQty;
                    }
                }
                renderCart();
            };

            window.removeCartItem = function(index) {
                cart.splice(index, 1);
                renderCart();
            };

            // --- Modifier selection modal ------------------------------------
            const modEl = document.getElementById('modifierModal');
            const modGroupsEl = document.getElementById('modGroups');
            const modAddBtn = document.getElementById('modAddBtn');
            const modHint = document.getElementById('modHint');
            let modProduct = null;
            let modQty = 1;

            function openModifierModal(product) {
                modProduct = product;
                modQty = 1;
                const groups = productModifiers[product.id];

                document.getElementById('modProductName').innerText = product.name;
                document.getElementById('modProductBase').innerText = 'Rp' + formatRupiah(product.price);
                document.getElementById('modProductThumb').innerHTML = product.image
                    ? `<img src="${escapeHtml(product.image)}" alt="">`
                    : '<i class="bi bi-cup-hot"></i>';
                document.getElementById('modNotes').value = '';

                // Required single-choice → radio. Everything else → checkbox,
                // so optional choices can be un-ticked.
                modGroupsEl.innerHTML = groups.map(g => {
                    const useRadio = g.max === 1 && g.min >= 1;
                    return `
                        <div class="mb-3 mod-group" data-id="${g.id}" data-min="${g.min}" data-max="${g.max}" data-name="${escapeHtml(g.name)}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">${escapeHtml(g.name)}</span>
                                <span class="badge ${g.min >= 1 ? 'bg-danger bg-opacity-10 text-danger' : 'bg-light text-secondary border'}">${escapeHtml(g.label)}</span>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                ${g.options.map(o => `
                                    <input type="${useRadio ? 'radio' : 'checkbox'}" class="btn-check mod-option"
                                           name="mg-${g.id}" id="mo-${o.id}" value="${o.id}"
                                           data-price="${o.price}" data-label="${escapeHtml(o.name)}"
                                           ${o.is_default ? 'checked' : ''} autocomplete="off">
                                    <label class="btn btn-outline-primary btn-sm px-3 py-2" for="mo-${o.id}">
                                        ${escapeHtml(o.name)}${o.price > 0 ? ` <small class="opacity-75">+${formatRupiah(o.price)}</small>` : ''}
                                    </label>
                                `).join('')}
                            </div>
                        </div>`;
                }).join('');

                updateModifierModal();
                (window.bootstrap || bootstrap).Modal.getOrCreateInstance(modEl).show();
            }

            function collectModifiers() {
                const result = { optionIds: [], labels: [], total: 0, missing: null };
                modGroupsEl.querySelectorAll('.mod-group').forEach(groupEl => {
                    const checked = groupEl.querySelectorAll('.mod-option:checked');
                    if (!result.missing && checked.length < parseInt(groupEl.dataset.min)) {
                        result.missing = groupEl.dataset.name;
                    }
                    checked.forEach(input => {
                        result.optionIds.push(parseInt(input.value));
                        result.labels.push(input.dataset.label);
                        result.total += parseFloat(input.dataset.price);
                    });
                });
                return result;
            }

            function updateModifierModal() {
                const mods = collectModifiers();
                document.getElementById('modQty').innerText = modQty;
                document.getElementById('modTotal').innerText = 'Rp' + formatRupiah((modProduct.price + mods.total) * modQty);
                modAddBtn.disabled = !!mods.missing;
                modHint.classList.toggle('d-none', !mods.missing);
                modHint.innerText = mods.missing ? 'Pilih ' + mods.missing + ' terlebih dahulu.' : '';
            }

            // Enforce per-group maximum on checkbox groups.
            modGroupsEl.addEventListener('change', function(e) {
                const input = e.target;
                if (!input.classList.contains('mod-option')) return;
                const groupEl = input.closest('.mod-group');
                const max = parseInt(groupEl.dataset.max);

                if (input.type === 'checkbox' && input.checked && max > 0) {
                    const checked = groupEl.querySelectorAll('.mod-option:checked');
                    if (max === 1) {
                        checked.forEach(cb => { if (cb !== input) cb.checked = false; });
                    } else if (checked.length > max) {
                        input.checked = false;
                        modHint.classList.remove('d-none');
                        modHint.innerText = groupEl.dataset.name + ' maksimal ' + max + ' pilihan.';
                        return;
                    }
                }
                updateModifierModal();
            });

            document.getElementById('modQtyMinus').addEventListener('click', () => { if (modQty > 1) { modQty--; updateModifierModal(); } });
            document.getElementById('modQtyPlus').addEventListener('click', () => { modQty++; updateModifierModal(); });

            modAddBtn.addEventListener('click', function() {
                const mods = collectModifiers();
                if (mods.missing) return;
                const notes = document.getElementById('modNotes').value.trim();
                if (addLine(modProduct, mods, notes, modQty)) {
                    (window.bootstrap || bootstrap).Modal.getInstance(modEl).hide();
                }
            });

            document.getElementById('btnClearCart').addEventListener('click', function() {
                cart = [];
                renderCart();
            });

            discountInput.addEventListener('input', function() {
                discountAmount = parseFloat(this.value) || 0;
                renderTotals();
            });

            cashPaidInput.addEventListener('input', function() {
                calculateChange();
            });

            btnExactCash.addEventListener('click', function() {
                const total = getFinalTotal();
                cashPaidInput.value = total;
                calculateChange();
            });

            window.addQuickCash = function(amount) {
                const current = parseFloat(cashPaidInput.value) || 0;
                cashPaidInput.value = current + amount;
                calculateChange();
            };

            window.clearCashInput = function() {
                cashPaidInput.value = '';
                calculateChange();
            };

            function getSubtotal() {
                return cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            }

            function getFinalTotal() {
                return calculateCharges(getSubtotal(), discountAmount).final;
            }

            function calculateCharges(subtotal, discount) {
                const base = Math.max(0, subtotal - discount);
                const service = Math.round(base * serviceChargeRate) / 100;
                const tax = pricesIncludeTax
                    ? Math.round((base - (taxRate > 0 ? base / (1 + taxRate / 100) : base)) * 100) / 100
                    : Math.round((base + service) * taxRate) / 100;
                return { service, tax, final: Math.max(0, base + service + (pricesIncludeTax ? 0 : tax)) };
            }

            function renderCart() {
                if (cart.length === 0) {
                    cartEmptyState.style.display = 'block';
                    cartList.innerHTML = '';
                    cartItemCount.innerText = '0 item dipilih';
                } else {
                    cartEmptyState.style.display = 'none';
                    const totalCount = cart.reduce((sum, i) => sum + i.quantity, 0);
                    cartItemCount.innerText = totalCount + ' item dipilih';

                    cartList.innerHTML = cart.map((item, index) => `
                        <div class="card border-0 bg-light p-2 rounded-3 d-flex flex-row gap-2 align-items-center">
                            <div class="pos-cart-thumb">
                                ${item.image
                                    ? `<img src="${escapeHtml(item.image)}" alt="">`
                                    : `<i class="bi bi-box-seam"></i>`}
                            </div>
                            <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div class="min-w-0 me-2">
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.9rem;">${escapeHtml(item.name)}</div>
                                    ${item.modifier_labels.length ? `<div class="text-muted text-truncate" style="font-size: 0.75rem;">${item.modifier_labels.map(escapeHtml).join(', ')}</div>` : ''}
                                    ${item.notes ? `<div class="text-warning-emphasis fst-italic text-truncate" style="font-size: 0.75rem;"><i class="bi bi-pencil-square"></i> ${escapeHtml(item.notes)}</div>` : ''}
                                </div>
                                <button type="button" class="btn btn-sm text-danger p-0 border-0" onclick="removeCartItem(${index})">
                                    <i class="bi bi-x-circle-fill"></i>
                                </button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-primary fw-bold small">Rp${formatRupiah(item.price)}</span>
                                <div class="d-flex align-items-center gap-1 bg-white border rounded-2 p-1">
                                    <button type="button" class="btn btn-xs btn-light border-0 fw-bold px-2 py-0" onclick="updateCartQty(${index}, -1)">-</button>
                                    <span class="fw-bold px-2 small">${item.quantity}</span>
                                    <button type="button" class="btn btn-xs btn-light border-0 fw-bold px-2 py-0" onclick="updateCartQty(${index}, 1)">+</button>
                                </div>
                                <span class="fw-extrabold text-dark small">Rp${formatRupiah(item.price * item.quantity)}</span>
                            </div>
                            </div>
                        </div>
                    `).join('');
                }

                renderTotals();
                syncBillButtons();
            }

            function renderTotals() {
                const subtotal = getSubtotal();
                const charges = calculateCharges(subtotal, discountAmount);
                const finalTotal = charges.final;

                displaySubtotal.innerText = 'Rp' + formatRupiah(subtotal);
                displayServiceCharge.innerText = 'Rp' + formatRupiah(charges.service);
                displayTax.innerText = 'Rp' + formatRupiah(charges.tax);
                displayFinalTotal.innerText = 'Rp' + formatRupiah(finalTotal);

                calculateChange();
            }

            function calculateChange() {
                const finalTotal = getFinalTotal();
                const cashPaid = parseFloat(cashPaidInput.value) || 0;
                const change = cashPaid - finalTotal;

                if (cart.length > 0) {
                    btnCheckoutCash.disabled = false;
                    if (cashPaid >= finalTotal && cashPaid > 0) {
                        displayChange.innerText = 'Rp' + formatRupiah(change);
                        displayChange.className = 'fw-bold fs-5 text-success';
                    } else if (cashPaid > 0) {
                        displayChange.innerText = '-Rp' + formatRupiah(Math.abs(change));
                        displayChange.className = 'fw-bold fs-5 text-danger';
                    } else {
                        displayChange.innerText = 'Rp0';
                        displayChange.className = 'fw-bold fs-5 text-muted';
                    }
                } else {
                    displayChange.innerText = 'Rp0';
                    displayChange.className = 'fw-bold fs-5 text-muted';
                    btnCheckoutCash.disabled = true;
                }
            }

            // QRIS Modal Open Trigger
            document.getElementById('btnOpenQrisModal').addEventListener('click', function() {
                if (cart.length === 0) {
                    notify('warning', 'Keranjang belanja masih kosong!');
                    return;
                }
                const orderError = validateOrderInfo();
                if (orderError) {
                    notify('warning', orderError);
                    return;
                }
                const total = getFinalTotal();
                document.getElementById('qrisModalTotal').innerText = 'Rp' + formatRupiah(total);
                const qrisModalEl = document.getElementById('qrisModal');
                const modal = window.bootstrap ? new window.bootstrap.Modal(qrisModalEl) : new bootstrap.Modal(qrisModalEl);
                modal.show();
            });

            // Checkout Cash Action
            btnCheckoutCash.addEventListener('click', function() {
                const paid = parseFloat(cashPaidInput.value) || 0;
                const total = getFinalTotal();
                if (paid < total) {
                    notify('warning', 'Uang yang diterima kurang. Total belanja Rp' + formatRupiah(total) + ', uang diterima baru Rp' + formatRupiah(paid) + '. Mohon lengkapi jumlah uang tunai.');
                    return;
                }
                processCheckout('cash', paid);
            });

            // Checkout QRIS Action
            document.getElementById('btnCheckoutQris').addEventListener('click', function() {
                const qrisModalEl = document.getElementById('qrisModal');
                const bs = window.bootstrap || bootstrap;
                const modal = bs.Modal.getInstance(qrisModalEl);
                if (modal) modal.hide();
                processCheckout('qris', getFinalTotal());
            });

            // --- Order info (type / table / customer) ------------------------
            const tableSelect = document.getElementById('tableSelect');
            const platformSelect = document.getElementById('platformSelect');
            const customerNameInput = document.getElementById('customerName');

            function getOrderType() {
                return document.querySelector('input[name="order_type"]:checked').value;
            }

            function syncOrderTypeUi() {
                const type = getOrderType();
                document.getElementById('tableWrap').classList.toggle('d-none', type !== 'dine_in');
                document.getElementById('platformWrap').classList.toggle('d-none', type !== 'online');
                customerNameInput.placeholder = type === 'online' ? 'Nama / ID pesanan online' : 'Nama pelanggan (opsional)';
                const hint = document.getElementById('noTableHint');
                if (hint) hint.classList.toggle('d-none', type !== 'dine_in');
            }
            document.querySelectorAll('input[name="order_type"]').forEach(r => r.addEventListener('change', syncOrderTypeUi));
            syncOrderTypeUi();

            // Returns an error message, or null when the order info is complete.
            function validateOrderInfo() {
                const type = getOrderType();
                if (type === 'dine_in' && !tableSelect.value) return 'Pilih meja untuk pesanan dine-in.';
                if (type === 'online' && !platformSelect.value) return 'Pilih platform pesanan online.';
                return null;
            }

            function getOrderInfo() {
                const type = getOrderType();
                return {
                    order_type: type,
                    table_id: type === 'dine_in' ? parseInt(tableSelect.value) : null,
                    online_platform: type === 'online' ? platformSelect.value : null,
                    customer_name: customerNameInput.value.trim() || null,
                };
            }

            function resetOrderInfo() {
                tableSelect.value = '';
                platformSelect.value = '';
                customerNameInput.value = '';
            }

            function processCheckout(method, paidAmount) {
                if (cart.length === 0) {
                    notify('warning', 'Keranjang belanja kosong!');
                    return;
                }
                const orderError = validateOrderInfo();
                if (orderError) {
                    notify('warning', orderError);
                    return;
                }

                const payload = {
                    items: cartItemsPayload(),
                    payment_method: method,
                    discount_amount: discountAmount,
                    paid_amount: paidAmount,
                    ...getOrderInfo(),
                };

                fetch("{{ route('pos.checkout') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Reset Cart
                        cart = [];
                        discountInput.value = 0;
                        discountAmount = 0;
                        cashPaidInput.value = '';
                        resetOrderInfo();
                        renderCart();

                        // Refresh remaining stock immediately so the grid reflects
                        // the quantities just sold (and any concurrent sales).
                        refreshStocks();

                        showReceipt(data);
                    } else {
                        notify('error', 'Gagal memproses transaksi: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    notify('error', 'Terjadi kesalahan jaringan/server.');
                });
            }


            function cartItemsPayload() {
                return cart.map(i => ({
                    id: i.id,
                    name: i.name,
                    quantity: i.quantity,
                    modifier_option_ids: i.modifier_option_ids,
                    notes: i.notes,
                }));
            }

            function showReceipt(data) {
                // Save transaction reference for Bluetooth direct printing
                lastProcessedTransaction = data.transaction;

                const savedSize = localStorage.getItem('pos_paper_size') || '58mm';
                document.getElementById('receiptContainer').innerHTML = data.receipt_html;
                setPaperSize(savedSize);
                document.getElementById(savedSize === '80mm' ? 'paper80' : 'paper58').checked = true;

                (window.bootstrap || bootstrap).Modal.getOrCreateInstance(document.getElementById('receiptModal')).show();
            }

            // --- Open bills (bayar nanti) -----------------------------------
            const billsUrl = @json(route('pos.bills.index'));
            const receiptUrlTemplate = @json(route('pos.receipt', ['transaction' => '__ID__']));
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const Modal = () => (window.bootstrap || bootstrap).Modal;
            // focus:false so SweetAlert inputs work on top of these modals.
            const billsModal = () => Modal().getOrCreateInstance(document.getElementById('billsModal'), { focus: false });
            const billModal = () => Modal().getOrCreateInstance(document.getElementById('billModal'), { focus: false });

            let openBills = [];
            let activeBill = null;   // bill we are adding items to
            let viewingBill = null;  // bill shown in the detail modal

            async function api(url, method = 'GET', body = null) {
                const res = await fetch(url, {
                    method,
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: body ? JSON.stringify(body) : null,
                });
                let data = {};
                try { data = await res.json(); } catch (e) { /* non-JSON error page */ }
                if (!res.ok || data.success === false) {
                    throw new Error(data.message || 'Terjadi kesalahan jaringan/server.');
                }
                return data;
            }

            function billLabel(b) {
                const type = { dine_in: 'Dine-in', take_away: 'Take Away', online: 'Online' }[b.order_type] || '';
                const who = b.table_name || b.customer_name || b.invoice_number;
                return who + (b.table_name && b.customer_name ? ' · ' + b.customer_name : '') + (b.online_platform ? ' · ' + b.online_platform : '') + ' (' + type + ')';
            }

            function minutesAgo(iso) {
                const m = Math.max(0, Math.round((Date.now() - new Date(iso).getTime()) / 60000));
                return m < 60 ? m + ' mnt' : Math.floor(m / 60) + ' j ' + (m % 60) + ' mnt';
            }

            function refreshBills() {
                return api(billsUrl).then(data => {
                    openBills = data.bills;
                    document.getElementById('openBillCount').innerText = openBills.length;

                    // Mark occupied tables in the table picker.
                    const occupied = new Set(openBills.map(b => b.app_table_id).filter(Boolean));
                    tableSelect.querySelectorAll('option[value]:not([value=""])').forEach(opt => {
                        opt.dataset.name = opt.dataset.name || opt.textContent.trim();
                        opt.textContent = opt.dataset.name + (occupied.has(parseInt(opt.value)) ? ' • terisi' : '');
                    });

                    if (document.getElementById('billsModal').classList.contains('show')) renderBillsList();
                }).catch(() => { /* next poll retries */ });
            }

            function renderBillsList() {
                const list = document.getElementById('billsList');
                document.getElementById('billsEmpty').classList.toggle('d-none', openBills.length > 0);
                list.innerHTML = openBills.map(b => `
                    <div class="col-sm-6">
                        <button type="button" class="btn btn-light border w-100 text-start p-3 h-100 bill-card" data-id="${b.id}">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <div class="min-w-0">
                                    <div class="fw-bold text-truncate">${escapeHtml(b.table_name || b.customer_name || b.invoice_number)}</div>
                                    <small class="text-muted d-block text-truncate">${escapeHtml(billLabel(b))}</small>
                                </div>
                                <span class="badge bg-dark">#${b.queue_number ?? '-'}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-end mt-2">
                                <small class="text-muted"><i class="bi bi-clock me-1"></i>${minutesAgo(b.created_at)} · ${b.details_count} item</small>
                                <span class="fw-bold text-primary">Rp${formatRupiah(b.total_amount)}</span>
                            </div>
                        </button>
                    </div>`).join('');
            }

            document.getElementById('billsList').addEventListener('click', e => {
                const card = e.target.closest('.bill-card');
                if (card) openBillDetail(parseInt(card.dataset.id));
            });

            document.getElementById('btnOpenBills').addEventListener('click', () => {
                refreshBills().then(renderBillsList);
                renderBillsList();
                billsModal().show();
            });

            function syncBillButtons() {
                document.getElementById('btnSaveBill').disabled = cart.length === 0;
                document.getElementById('btnSendToBill').disabled = cart.length === 0;
            }

            function enterBillMode(bill) {
                activeBill = bill;
                document.getElementById('activeBillLabel').innerText = billLabel(bill);
                document.getElementById('activeBillBanner').classList.remove('d-none');
                document.getElementById('payNowSection').classList.add('d-none');
                document.getElementById('orderInfoPanel').classList.add('d-none');
                syncBillButtons();
            }

            function exitBillMode() {
                activeBill = null;
                document.getElementById('activeBillBanner').classList.add('d-none');
                document.getElementById('payNowSection').classList.remove('d-none');
                document.getElementById('orderInfoPanel').classList.remove('d-none');
            }
            document.getElementById('btnExitBillMode').addEventListener('click', exitBillMode);

            function clearCartAfterOrder() {
                cart = [];
                discountInput.value = 0;
                discountAmount = 0;
                cashPaidInput.value = '';
                renderCart();
                refreshStocks();
                refreshBills();
            }

            async function sendItemsToBill(bill) {
                try {
                    await api(`${billsUrl}/${bill.id}/items`, 'POST', { items: cartItemsPayload() });
                    clearCartAfterOrder();
                    exitBillMode();
                    notify('success', 'Pesanan ditambahkan ke ' + (bill.table_name || bill.customer_name || bill.invoice_number) + '.');
                } catch (err) {
                    notify('error', err.message);
                }
            }
            document.getElementById('btnSendToBill').addEventListener('click', () => {
                if (activeBill && cart.length) sendItemsToBill(activeBill);
            });

            document.getElementById('btnSaveBill').addEventListener('click', async () => {
                if (cart.length === 0) return;
                const orderError = validateOrderInfo();
                if (orderError) { notify('warning', orderError); return; }

                const info = getOrderInfo();
                if (info.order_type !== 'dine_in' && !info.customer_name) {
                    notify('warning', 'Isi nama pelanggan agar bill take away / online mudah dikenali.');
                    return;
                }

                // Table already has a running bill → offer to add to it.
                const existing = info.table_id && openBills.find(b => b.app_table_id === info.table_id);
                if (existing) {
                    const answer = await Swal.fire({
                        icon: 'question',
                        title: existing.table_name + ' sudah punya bill',
                        text: 'Tambahkan pesanan ini ke bill ' + existing.invoice_number + '?',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, tambahkan',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#1d4ed8',
                    });
                    if (answer.isConfirmed) sendItemsToBill(existing);
                    return;
                }

                try {
                    const data = await api(billsUrl, 'POST', { items: cartItemsPayload(), ...info });
                    clearCartAfterOrder();
                    resetOrderInfo();
                    const t = data.transaction;
                    notify('success', 'Bill dibuka untuk ' + (t.table_name || t.customer_name) + ' · Antrean #' + t.queue_number + '. Bayar lewat tombol Bill Terbuka.');
                } catch (err) {
                    notify('error', err.message);
                    refreshBills();
                }
            });

            // --- Bill detail & payment ----------------------------------------
            const billItemsEl = document.getElementById('billItems');
            const billSplitToggle = document.getElementById('billSplitToggle');
            const billDiscount = document.getElementById('billDiscount');
            const billCashPaid = document.getElementById('billCashPaid');

            async function openBillDetail(id) {
                try {
                    const data = await api(`${billsUrl}/${id}`);
                    viewingBill = data.bill;
                    billSplitToggle.checked = false;
                    billDiscount.value = 0;
                    billCashPaid.value = '';
                    document.getElementById('billCash').checked = true;
                    renderBillDetail();
                    billsModal().hide();
                    billModal().show();
                } catch (err) {
                    notify('error', err.message);
                    refreshBills();
                }
            }

            function renderBillDetail() {
                const b = viewingBill;
                const opened = new Date(b.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                document.getElementById('billTitle').innerText = billLabel(b);
                document.getElementById('billSubtitle').innerText =
                    `${b.invoice_number} · Antrean #${b.queue_number ?? '-'} · dibuka ${opened}` + (b.opener ? ' oleh ' + b.opener.name : '');

                const split = billSplitToggle.checked;
                billItemsEl.innerHTML = b.details.map(d => {
                    const mods = (d.modifiers || []).map(m => m.option).join(', ');
                    return `
                    <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom">
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-truncate">${escapeHtml(d.product ? d.product.name : 'Produk')}</div>
                            ${mods ? `<small class="text-muted d-block text-truncate">${escapeHtml(mods)}</small>` : ''}
                            ${d.notes ? `<small class="fst-italic text-warning-emphasis d-block text-truncate">* ${escapeHtml(d.notes)}</small>` : ''}
                            <small class="text-muted">${d.quantity} × Rp${formatRupiah(d.selling_price)}</small>
                        </div>
                        ${split ? `
                            <div class="input-group input-group-sm flex-shrink-0" style="width: 120px;">
                                <span class="input-group-text">Bayar</span>
                                <input type="number" class="form-control text-center bill-qty" min="0" max="${d.quantity}" value="${d.quantity}"
                                       data-id="${d.id}" data-price="${d.selling_price}">
                            </div>` : ''}
                        <span class="fw-bold flex-shrink-0 text-end" style="min-width: 90px;">Rp${formatRupiah(d.subtotal)}</span>
                    </div>`;
                }).join('');
                updateBillTotals();
            }

            function selectedBillLines() {
                return [...billItemsEl.querySelectorAll('.bill-qty')].map(input => {
                    const max = parseInt(input.max);
                    let qty = parseInt(input.value) || 0;
                    qty = Math.min(Math.max(qty, 0), max);
                    return { detail_id: parseInt(input.dataset.id), quantity: qty, price: parseFloat(input.dataset.price) };
                });
            }

            function billAmounts() {
                const fullTotal = parseFloat(viewingBill.total_amount);
                const subtotal = billSplitToggle.checked
                    ? selectedBillLines().reduce((s, l) => s + l.quantity * l.price, 0)
                    : fullTotal;
                const discount = Math.min(parseFloat(billDiscount.value) || 0, subtotal);
                return { subtotal, total: Math.max(0, subtotal - discount), remaining: fullTotal - subtotal };
            }

            function updateBillTotals() {
                const a = billAmounts();
                document.getElementById('billSubtotal').innerText = 'Rp' + formatRupiah(a.subtotal);
                document.getElementById('billTotal').innerText = 'Rp' + formatRupiah(a.total);
                const rem = document.getElementById('billRemaining');
                rem.classList.toggle('d-none', !(billSplitToggle.checked && a.remaining > 0));
                rem.innerText = 'Sisa di bill setelah bayar: Rp' + formatRupiah(a.remaining);

                const isCash = document.getElementById('billCash').checked;
                document.getElementById('billCashWrap').classList.toggle('d-none', !isCash);
                const paid = parseFloat(billCashPaid.value) || 0;
                const change = paid - a.total;
                const changeEl = document.getElementById('billChange');
                changeEl.innerText = (change < 0 ? '-Rp' : 'Rp') + formatRupiah(Math.abs(change));
                changeEl.className = 'fw-bold ' + (paid === 0 ? 'text-muted' : change < 0 ? 'text-danger' : 'text-success');
            }

            billSplitToggle.addEventListener('change', renderBillDetail);
            billItemsEl.addEventListener('input', updateBillTotals);
            [billDiscount, billCashPaid].forEach(el => el.addEventListener('input', updateBillTotals));
            document.querySelectorAll('input[name="bill_method"]').forEach(r => r.addEventListener('change', updateBillTotals));
            document.getElementById('billExact').addEventListener('click', () => {
                billCashPaid.value = billAmounts().total;
                updateBillTotals();
            });

            document.getElementById('btnBillPay').addEventListener('click', async () => {
                const a = billAmounts();
                const method = document.querySelector('input[name="bill_method"]:checked').value;
                const body = {
                    payment_method: method,
                    discount_amount: parseFloat(billDiscount.value) || 0,
                    paid_amount: method === 'cash' ? (parseFloat(billCashPaid.value) || 0) : a.total,
                };

                if (billSplitToggle.checked) {
                    const lines = selectedBillLines().filter(l => l.quantity > 0);
                    if (!lines.length) { notify('warning', 'Pilih minimal satu item untuk dibayar.'); return; }
                    body.lines = lines.map(l => ({ detail_id: l.detail_id, quantity: l.quantity }));
                }
                if (method === 'cash' && body.paid_amount < a.total) {
                    notify('warning', 'Uang yang diterima kurang dari total Rp' + formatRupiah(a.total) + '.');
                    return;
                }

                const btn = document.getElementById('btnBillPay');
                btn.disabled = true;
                try {
                    const data = await api(`${billsUrl}/${viewingBill.id}/pay`, 'POST', body);
                    billModal().hide();
                    refreshBills();
                    refreshStocks();
                    showReceipt(data);
                } catch (err) {
                    notify('error', err.message);
                } finally {
                    btn.disabled = false;
                }
            });

            document.getElementById('btnBillPrint').addEventListener('click', () => {
                window.open(receiptUrlTemplate.replace('__ID__', viewingBill.id), '_blank');
            });

            document.getElementById('btnBillAddItems').addEventListener('click', () => {
                billModal().hide();
                enterBillMode(viewingBill);
            });

            document.getElementById('btnBillCancel').addEventListener('click', async () => {
                const result = await Swal.fire({
                    icon: 'warning',
                    title: 'Batalkan bill ini?',
                    text: 'Stok semua item akan dikembalikan.',
                    input: 'text',
                    inputPlaceholder: 'Alasan pembatalan (wajib)',
                    inputValidator: v => (!v || v.trim().length < 3) ? 'Isi alasan minimal 3 huruf.' : null,
                    showCancelButton: true,
                    confirmButtonText: 'Batalkan Bill',
                    cancelButtonText: 'Kembali',
                    confirmButtonColor: '#dc2626',
                });
                if (!result.isConfirmed) return;
                try {
                    await api(`${billsUrl}/${viewingBill.id}/cancel`, 'POST', { reason: result.value.trim() });
                    billModal().hide();
                    if (activeBill && activeBill.id === viewingBill.id) exitBillMode();
                    refreshBills();
                    refreshStocks();
                    notify('success', 'Bill dibatalkan dan stok dikembalikan.');
                } catch (err) {
                    notify('error', err.message);
                }
            });

            refreshBills();

            // Product names/URLs come from data-* attributes (already decoded),
            // so escape before injecting into innerHTML.
            function escapeHtml(str) {
                return String(str ?? '').replace(/[&<>"']/g, c => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
                }[c]));
            }

            function formatRupiah(num) {
                return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }

            // --- Real-time stock sync ---------------------------------------
            // Update a single product's displayed remaining stock (grid badge,
            // data attribute, and any matching cart entry's max-qty guard).
            function applyStock(id, stock, minStock) {
                const item = document.querySelector('.product-item[data-id="' + id + '"]');
                if (!item) return;

                item.dataset.stock = stock;
                if (minStock !== undefined && minStock !== null) {
                    item.dataset.minStock = minStock;
                }

                const badge = document.getElementById('stockBadge-' + id);
                if (badge) {
                    const min = parseInt(item.dataset.minStock || '0', 10);
                    badge.innerText = 'Stok: ' + stock;
                    badge.className = 'badge rounded-pill pos-thumb-stock ' +
                        (stock <= min ? 'bg-danger' : 'bg-success');
                }

                // Keep the in-memory cart's stock ceiling in sync.
                cart.forEach(i => { if (i.id === id) i.stock = stock; });
            }

            // Pull current stock for all active products from the server so the
            // remaining quantities stay live across sales and multiple cashiers.
            function refreshStocks() {
                fetch("{{ route('pos.stocks') }}", { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && Array.isArray(data.stocks)) {
                            data.stocks.forEach(s => applyStock(s.id, s.stock, s.min_stock));
                        }
                    })
                    .catch(() => { /* transient network issue: next poll retries */ });
            }

            // Poll periodically for near real-time remaining stock.
            setInterval(refreshStocks, 15000);
            setInterval(refreshBills, 15000);

            // Web Bluetooth Thermal Printer Connection State
            let bluetoothDevice = null;
            let bluetoothCharacteristic = null;
            let lastProcessedTransaction = null;

            window.connectBluetoothPrinter = async function() {
                if (!navigator.bluetooth) {
                    notify('error', 'Web Bluetooth tidak didukung browser ini. Gunakan Google Chrome/Edge di Android/Windows/Mac, atau cetak melalui driver bawaan OS.');
                    return;
                }

                try {
                    bluetoothDevice = await navigator.bluetooth.requestDevice({
                        acceptAllDevices: true,
                        optionalServices: [
                            '000018f0-0000-1000-8000-00805f9b34fb',
                            '0000e7e0-0000-1000-8000-00805f9b34fb',
                            '0000ff00-0000-1000-8000-00805f9b34fb',
                            '49535343-fe7d-4ae5-8fa9-9fafd205e455'
                        ]
                    });

                    const statusBtn = document.getElementById('btnConnectBluetoothPrinter');
                    const statusText = document.getElementById('bluetoothStatusText');
                    statusText.innerText = 'Menghubungkan...';

                    const server = await bluetoothDevice.gatt.connect();
                    const services = await server.getPrimaryServices();

                    for (const service of services) {
                        const characteristics = await service.getCharacteristics();
                        for (const char of characteristics) {
                            if (char.properties.write || char.properties.writeWithoutResponse) {
                                bluetoothCharacteristic = char;
                                break;
                            }
                        }
                        if (bluetoothCharacteristic) break;
                    }

                    if (bluetoothCharacteristic) {
                        statusText.innerText = 'Bluetooth: ' + bluetoothDevice.name;
                        statusBtn.className = 'btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-semibold';
                        notify('success', 'Berhasil terhubung ke Printer Bluetooth: ' + bluetoothDevice.name);
                    } else {
                        statusText.innerText = 'Gagal Saluran';
                        notify('error', 'Perangkat terhubung tetapi tidak ditemukan saluran ESC/POS.');
                    }
                } catch (error) {
                    console.error(error);
                    notify('error', 'Gagal menghubungkan printer: ' + error.message);
                }
            };

            window.setPaperSize = function(size) {
                localStorage.setItem('pos_paper_size', size);
                const area = document.getElementById('thermal-receipt-area');
                if (area) {
                    if (size === '80mm') {
                        area.style.width = '80mm';
                        area.style.maxWidth = '400px';
                        area.style.fontSize = '13px';
                    } else {
                        area.style.width = '58mm';
                        area.style.maxWidth = '300px';
                        area.style.fontSize = '11px';
                    }
                }
            };

            window.printReceipt = async function() {
                // Try Bluetooth ESC/POS Direct Print First
                if (bluetoothCharacteristic && lastProcessedTransaction) {
                    try {
                        let encoder = new TextEncoder();
                        let commands = [];

                        // ESC @ Init
                        commands.push(0x1B, 0x40);

                        // Center Align
                        commands.push(0x1B, 0x61, 0x01);
                        commands.push(...encoder.encode("SMART KASIR UMKM\nJl. Raya Utama No. 88\nTelp: 0812-3456-7890\n--------------------------------\n"));

                        // Left Align
                        commands.push(0x1B, 0x61, 0x00);
                        const trx = lastProcessedTransaction;
                        if (trx.queue_number) {
                            commands.push(0x1B, 0x61, 0x01, 0x1D, 0x21, 0x11); // center + double size
                            commands.push(...encoder.encode(`ANTREAN ${trx.queue_number}\n`));
                            commands.push(0x1D, 0x21, 0x00, 0x1B, 0x61, 0x00); // normal size + left
                        }
                        const typeLabel = { dine_in: 'Dine-in', take_away: 'Take Away', online: 'Online' }[trx.order_type] || '';
                        let orderLine = typeLabel;
                        if (trx.table_name) orderLine += ' - ' + trx.table_name;
                        if (trx.online_platform) orderLine += ' - ' + trx.online_platform;
                        commands.push(...encoder.encode(`${orderLine}\n`));
                        if (trx.customer_name) commands.push(...encoder.encode(`Nama: ${trx.customer_name}\n`));
                        commands.push(...encoder.encode(`No: ${trx.invoice_number}\nKasir: ${trx.user ? trx.user.name : 'Kasir'}\n--------------------------------\n`));

                        if (trx.details) {
                            trx.details.forEach(d => {
                                const name = d.product ? d.product.name : 'Produk';
                                commands.push(...encoder.encode(`${name}\n`));
                                (d.modifiers || []).forEach(m => commands.push(...encoder.encode(`  + ${m.option}\n`)));
                                if (d.notes) commands.push(...encoder.encode(`  * ${d.notes}\n`));
                                commands.push(...encoder.encode(` ${d.quantity} x ${d.selling_price} = Rp${d.subtotal}\n`));
                            });
                        }

                        commands.push(...encoder.encode(`--------------------------------\nTOTAL   : Rp${trx.final_amount}\nBayar   : Rp${trx.paid_amount}\nKembali : Rp${trx.change_amount}\n--------------------------------\n*** TERIMA KASIH ***\n\n\n`));
                        commands.push(0x1D, 0x56, 0x41, 0x03);

                        const uint8 = new Uint8Array(commands);
                        const chunkSize = 80;
                        for (let i = 0; i < uint8.length; i += chunkSize) {
                            await bluetoothCharacteristic.writeValue(uint8.slice(i, i + chunkSize));
                        }
                        return;
                    } catch (e) {
                        console.warn('Bluetooth print failed, fallback to native browser print:', e);
                    }
                }

                // Fallback to Native OS / USB Driver Print
                const savedSize = localStorage.getItem('pos_paper_size') || '58mm';
                window.setPaperSize(savedSize);
                window.print();
            };
        });
    </script>
</x-pos-layout>
