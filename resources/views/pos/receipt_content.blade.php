@if(!isset($is_modal) || !$is_modal)
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi #{{ $transaction->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        #thermal-receipt-area {
            background: #ffffff;
            width: 58mm;
            padding: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border-radius: 4px;
            color: #000;
        }
        .btn-print {
            margin-top: 15px;
            padding: 8px 20px;
            background-color: #0d6efd;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
        }
        @media print {
            body {
                background: none;
                padding: 0;
            }
            .btn-print {
                display: none;
            }
            #thermal-receipt-area {
                width: 100%;
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
@endif

    <div id="thermal-receipt-area" style="font-family: 'Courier New', Courier, monospace; color: #000; text-align: left; margin: 0 auto; width: 100%; max-width: 300px;">
        <div style="text-align: center; margin-bottom: 10px;">
            <h3 style="margin: 0; font-size: 15px; font-weight: bold;">{{ \App\Models\Setting::get('store_name', 'SMART KASIR UMKM') }}</h3>
            <p style="margin: 2px 0; font-size: 11px;">{{ \App\Models\Setting::get('store_address', 'Jl. Raya Utama No. 88, Indonesia') }}</p>
            <p style="margin: 2px 0; font-size: 11px;">Telp/WA: {{ \App\Models\Setting::get('store_phone', '0812-3456-7890') }}</p>
            <p style="margin: 4px 0 0 0;">--------------------------------</p>
        </div>

        <div style="font-size: 11px; margin-bottom: 8px;">
            <div>No. Trx : {{ $transaction->invoice_number }}</div>
            <div>Tanggal : {{ $transaction->created_at->format('d/m/Y H:i') }}</div>
            <div>Kasir   : {{ $transaction->user->name ?? 'Kasir' }}</div>
            <div>--------------------------------</div>
        </div>

        <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 8px;">
            @foreach($transaction->details as $detail)
                <tr>
                    <td colspan="3" style="font-weight: bold; padding-top: 2px;">{{ $detail->product->name ?? 'Produk' }}</td>
                </tr>
                <tr>
                    <td style="padding-bottom: 4px;">{{ $detail->quantity }} x {{ number_format($detail->selling_price, 0, ',', '.') }}</td>
                    <td style="text-align: right; padding-bottom: 4px;" colspan="2">Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>

        <div style="font-size: 11px;">
            <div>--------------------------------</div>
            <div style="display: flex; justify-content: space-between;">
                <span>Subtotal:</span>
                <span>Rp{{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
            </div>
            @if($transaction->discount_amount > 0)
            <div style="display: flex; justify-content: space-between;">
                <span>Diskon:</span>
                <span>-Rp{{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
            </div>
            @endif
            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 12px; margin-top: 4px;">
                <span>TOTAL:</span>
                <span>Rp{{ number_format($transaction->final_amount, 0, ',', '.') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 4px;">
                <span>Metode Bayar:</span>
                <span style="text-transform: uppercase; font-weight: bold;">{{ $transaction->payment_method }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Bayar:</span>
                <span>Rp{{ number_format($transaction->paid_amount, 0, ',', '.') }}</span>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <span>Kembali:</span>
                <span>Rp{{ number_format($transaction->change_amount, 0, ',', '.') }}</span>
            </div>
            <div>--------------------------------</div>
        </div>

        <div style="text-align: center; margin-top: 10px; font-size: 11px;">
            {!! nl2br(e(\App\Models\Setting::get('receipt_footer_note', "*** TERIMA KASIH ***\nBarang yang sudah dibeli tidak dapat ditukar/dikembalikan."))) !!}
        </div>
    </div>

@if(!isset($is_modal) || !$is_modal)
    <div style="margin-top: 15px; display: flex; gap: 10px;">
        <button class="btn-print" style="background-color: #6c757d;" onclick="changeStandalonePaperSize('58mm')">58mm (Standar)</button>
        <button class="btn-print" style="background-color: #6c757d;" onclick="changeStandalonePaperSize('80mm')">80mm (Lebar)</button>
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Struk</button>
    </div>

    <script>
        function changeStandalonePaperSize(size) {
            const area = document.getElementById('thermal-receipt-area');
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
    </script>
</body>
</html>
@endif
