<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekonsiliasi Shift Kasir</title>
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
        table.detail-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.detail-box td {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        table.detail-box td.label {
            font-weight: bold;
            color: #555;
            width: 40%;
        }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
        .text-primary { color: #0d6efd; }
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

    <div class="title">Laporan Audit Shift Kasir</div>

    <table class="detail-box">
        <tr>
            <td class="label">Nama Kasir Bertugas</td>
            <td><strong>{{ $shift->user->name ?? 'Kasir' }}</strong></td>
        </tr>
        <tr>
            <td class="label">Waktu Buka Shift</td>
            <td>{{ $shift->start_time ? $shift->start_time->format('d/m/Y H:i:s') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Waktu Tutup Shift</td>
            <td>{{ $shift->end_time ? $shift->end_time->format('d/m/Y H:i:s') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Modal Awal Laci Kasir</td>
            <td class="text-primary"><strong>Rp{{ number_format($shift->starting_cash, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td class="label">Total Omset Tunai Shift Ini</td>
            <td class="text-success"><strong>+Rp{{ number_format($shift->total_cash_sales, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td class="label">Total Omset QRIS (Non-Tunai)</td>
            <td>Rp{{ number_format($shift->total_qris_sales, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td class="label">Uang Wajib Ada di Laci</td>
            <td><strong>Rp{{ number_format($shift->expected_ending_cash, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td class="label">Total Uang Fisik Laci di-Input Kasir</td>
            <td class="text-primary"><strong style="font-size: 13px;">Rp{{ number_format($shift->actual_ending_cash, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td class="label">Selisih Uang Laci Kasir</td>
            <td>
                @if($shift->cash_difference == 0)
                    <span class="text-success"><strong>PAS (Rp0)</strong></span>
                @elseif($shift->cash_difference > 0)
                    <span class="text-primary"><strong>LEBIH (+Rp{{ number_format($shift->cash_difference, 0, ',', '.') }})</strong></span>
                @else
                    <span class="text-danger"><strong>KURANG (-Rp{{ number_format(abs($shift->cash_difference), 0, ',', '.') }})</strong></span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="label">Catatan / Alasan Kasir</td>
            <td>{{ $shift->notes ?: '-' }}</td>
        </tr>
    </table>

    <div class="footer">
        Dicetak otomatis oleh Smart Kasir POS pada {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
