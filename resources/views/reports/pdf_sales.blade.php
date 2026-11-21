<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan & Laba Rugi</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
            color: #0d6efd;
        }
        .header p {
            margin: 2px 0;
            color: #666;
        }
        .title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 15px;
            text-transform: uppercase;
        }
        .summary-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .summary-box td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            background-color: #f8f9fa;
        }
        .summary-box .val {
            font-size: 14px;
            font-weight: bold;
            margin-top: 4px;
        }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .text-primary { color: #0d6efd; }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.data-table th {
            background-color: #f1f3f5;
            border: 1px solid #dee2e6;
            padding: 6px;
            font-size: 10px;
            text-align: left;
        }
        table.data-table td {
            border: 1px solid #dee2e6;
            padding: 6px;
            font-size: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        @media print {
            .btn-print { display: none; }
            body { padding: 0; }
        }
        .btn-print {
            background-color: #0d6efd;
            color: #fff;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            float: right;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <button class="btn-print" onclick="window.print()">📄 Unduh / Cetak PDF</button>

    <div class="header">
        <h2>{{ \App\Models\Setting::get('store_name', 'SMART KASIR UMKM') }}</h2>
        <p>{{ \App\Models\Setting::get('store_address', 'Jl. Raya Utama No. 88, Indonesia') }} | Telp: {{ \App\Models\Setting::get('store_phone', '0812-3456-7890') }}</p>
    </div>

    <div class="title">Laporan Penjualan & Laba Rugi Toko</div>
    <p style="margin-bottom: 10px; font-size: 10px;"><strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s.d. {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>

    <!-- Summary Cards Table -->
    <table class="summary-box">
        <tr>
            <td width="20%">
                <div style="font-size: 9px; color: #666;">Total Omset</div>
                <div class="val text-primary">Rp{{ number_format($totalRevenue, 0, ',', '.') }}</div>
            </td>
            <td width="20%">
                <div style="font-size: 9px; color: #666;">Total Transaksi</div>
                <div class="val">{{ $totalTransactions }} Trx</div>
            </td>
            <td width="20%">
                <div style="font-size: 9px; color: #666;">Laba Kotor</div>
                <div class="val text-success">Rp{{ number_format($totalProfit, 0, ',', '.') }}</div>
            </td>
            <td width="20%">
                <div style="font-size: 9px; color: #666;">Beban Operasional</div>
                <div class="val text-danger">Rp{{ number_format($totalExpenses, 0, ',', '.') }}</div>
            </td>
            <td width="20%">
                <div style="font-size: 9px; color: #666;">Laba Bersih</div>
                <div class="val {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">Rp{{ number_format($netProfit, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="18%">No Invoice</th>
                <th width="15%">Waktu</th>
                <th width="12%">Kasir</th>
                <th width="10%">Metode</th>
                <th width="12%" class="text-right">Subtotal</th>
                <th width="10%" class="text-right">Diskon</th>
                <th width="18%" class="text-right">Total Akhir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $index => $trx)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $trx->invoice_number }}</strong></td>
                    <td>{{ ($trx->paid_at ?? $trx->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $trx->user->name ?? '-' }}</td>
                    <td class="text-center" style="text-transform: uppercase;">{{ $trx->payment_method }}</td>
                    <td class="text-right">Rp{{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                    <td class="text-right text-danger">Rp{{ number_format($trx->discount_amount, 0, ',', '.') }}</td>
                    <td class="text-right"><strong>Rp{{ number_format($trx->final_amount, 0, ',', '.') }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center py-4">Belum ada data transaksi pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Smart Kasir POS pada {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
