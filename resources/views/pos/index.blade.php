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
                         data-category="{{ $product->app_category_id }}">
                        <div class="pos-product-card p-3 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-light text-secondary border small">{{ $product->category->name ?? 'Umum' }}</span>
                                    <span class="badge {{ $product->stock <= $product->min_stock ? 'bg-danger' : 'bg-success bg-opacity-10 text-success' }} rounded-pill" id="stockBadge-{{ $product->id }}">
                                        Stok: {{ $product->stock }}
                                    </span>
                                </div>
                                <h6 class="fw-bold text-dark mb-1 text-truncate-2" style="font-size: 0.925rem; min-height: 2.6rem;">{{ $product->name }}</h6>
                                <small class="text-muted d-block text-truncate mb-2" style="font-size: 0.75rem;">SKU: {{ $product->sku }}</small>
                            </div>
                            <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary" style="font-size: 1rem;">Rp{{ number_format($product->selling_price, 0, ',', '.') }}</span>
                                <button type="button" class="btn btn-sm btn-primary btn-touch rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
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
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <h5 class="fw-bold text-dark m-0">TOTAL:</h5>
                    <h3 class="fw-extrabold text-primary m-0" id="displayFinalTotal">Rp0</h3>
                </div>
            </div>

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

            const cartContainer = document.getElementById('cartContainer');
            const cartEmptyState = document.getElementById('cartEmptyState');
            const cartList = document.getElementById('cartList');
            const cartItemCount = document.getElementById('cartItemCount');
            const displaySubtotal = document.getElementById('displaySubtotal');
            const displayFinalTotal = document.getElementById('displayFinalTotal');
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
                    const prod = {
                        id: parseInt(this.dataset.id),
                        sku: this.dataset.sku,
                        barcode: this.dataset.barcode,
                        name: this.dataset.name,
                        price: parseFloat(this.dataset.price),
                        stock: parseInt(this.dataset.stock)
                    };
                    addToCart(prod);
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
                            const prod = {
                                id: parseInt(productEl.dataset.id),
                                sku: productEl.dataset.sku,
                                barcode: productEl.dataset.barcode,
                                name: productEl.dataset.name,
                                price: parseFloat(productEl.dataset.price),
                                stock: parseInt(productEl.dataset.stock)
                            };
                            addToCart(prod);
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

            function addToCart(product) {
                if (product.stock <= 0) {
                    notify('warning', 'Stok produk ' + product.name + ' telah habis!');
                    return;
                }

                const existing = cart.find(i => i.id === product.id);
                if (existing) {
                    if (existing.quantity >= product.stock) {
                        notify('warning', 'Jumlah keranjang melebihi stok yang tersedia (' + product.stock + ')');
                        return;
                    }
                    existing.quantity++;
                } else {
                    cart.push({
                        id: product.id,
                        name: product.name,
                        price: product.price,
                        stock: product.stock,
                        quantity: 1
                    });
                }

                renderCart();
            }

            window.updateCartQty = function(id, delta) {
                const item = cart.find(i => i.id === id);
                if (item) {
                    const newQty = item.quantity + delta;
                    if (newQty <= 0) {
                        cart = cart.filter(i => i.id !== id);
                    } else if (newQty > item.stock) {
                        notify('warning', 'Stok tidak mencukupi. Maksimal: ' + item.stock);
                    } else {
                        item.quantity = newQty;
                    }
                }
                renderCart();
            };

            window.removeCartItem = function(id) {
                cart = cart.filter(i => i.id !== id);
                renderCart();
            };

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
                return Math.max(0, getSubtotal() - discountAmount);
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

                    cartList.innerHTML = cart.map(item => `
                        <div class="card border-0 bg-light p-2.5 rounded-3">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <div class="fw-bold text-dark text-truncate me-2" style="font-size: 0.9rem;">${item.name}</div>
                                <button type="button" class="btn btn-sm text-danger p-0 border-0" onclick="removeCartItem(${item.id})">
                                    <i class="bi bi-x-circle-fill"></i>
                                </button>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-primary fw-bold small">Rp${formatRupiah(item.price)}</span>
                                <div class="d-flex align-items-center gap-1 bg-white border rounded-2 p-1">
                                    <button type="button" class="btn btn-xs btn-light border-0 fw-bold px-2 py-0" onclick="updateCartQty(${item.id}, -1)">-</button>
                                    <span class="fw-bold px-2 small">${item.quantity}</span>
                                    <button type="button" class="btn btn-xs btn-light border-0 fw-bold px-2 py-0" onclick="updateCartQty(${item.id}, 1)">+</button>
                                </div>
                                <span class="fw-extrabold text-dark small">Rp${formatRupiah(item.price * item.quantity)}</span>
                            </div>
                        </div>
                    `).join('');
                }

                renderTotals();
            }

            function renderTotals() {
                const subtotal = getSubtotal();
                const finalTotal = getFinalTotal();

                displaySubtotal.innerText = 'Rp' + formatRupiah(subtotal);
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

            function processCheckout(method, paidAmount) {
                if (cart.length === 0) {
                    notify('warning', 'Keranjang belanja kosong!');
                    return;
                }

                const payload = {
                    items: cart,
                    payment_method: method,
                    discount_amount: discountAmount,
                    paid_amount: paidAmount,
                };

                fetch("{{ route('pos.checkout') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
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
                        renderCart();

                        // Refresh remaining stock immediately so the grid reflects
                        // the quantities just sold (and any concurrent sales).
                        refreshStocks();

                        // Save transaction reference for Bluetooth direct printing
                        lastProcessedTransaction = data.transaction;

                        // Apply saved paper size
                        const savedSize = localStorage.getItem('pos_paper_size') || '58mm';
                        setPaperSize(savedSize);
                        if (savedSize === '80mm') {
                            document.getElementById('paper80').checked = true;
                        } else {
                            document.getElementById('paper58').checked = true;
                        }

                        // Show Receipt Modal
                        document.getElementById('receiptContainer').innerHTML = data.receipt_html;
                        const bs = window.bootstrap || bootstrap;
                        const receiptModal = new bs.Modal(document.getElementById('receiptModal'));
                        receiptModal.show();
                    } else {
                        notify('error', 'Gagal memproses transaksi: ' + data.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    notify('error', 'Terjadi kesalahan jaringan/server.');
                });
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
                    badge.className = 'badge rounded-pill ' +
                        (stock <= min ? 'bg-danger' : 'bg-success bg-opacity-10 text-success');
                }

                // Keep the in-memory cart's stock ceiling in sync.
                const cartItem = cart.find(i => i.id === id);
                if (cartItem) cartItem.stock = stock;
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
                        commands.push(...encoder.encode(`No: ${trx.invoice_number}\nKasir: ${trx.user ? trx.user.name : 'Kasir'}\n--------------------------------\n`));

                        if (trx.details) {
                            trx.details.forEach(d => {
                                const name = d.product ? d.product.name : 'Produk';
                                commands.push(...encoder.encode(`${name}\n ${d.quantity} x ${d.selling_price} = Rp${d.subtotal}\n`));
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
