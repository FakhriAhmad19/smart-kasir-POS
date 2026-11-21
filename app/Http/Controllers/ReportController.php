<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use App\Services\SalesReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request, SalesReportService $salesReport)
    {
        $startDate = $request->input('start_date', now()->today()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->today()->format('Y-m-d'));
        $paymentMethod = $request->input('payment_method');
        $userId = $request->input('user_id');

        $transactions = $salesReport
            ->transactionsQuery($startDate, $endDate, $paymentMethod, $userId)
            ->paginate(15)
            ->withQueryString();

        $summary = $salesReport->summary($startDate, $endDate, $paymentMethod, $userId);
        $cafeBreakdown = $salesReport->cafeBreakdown($startDate, $endDate, $paymentMethod, $userId);
        $totalRevenue = $summary['totalRevenue'];
        $totalTransactions = $summary['totalTransactions'];
        $totalDiscount = $summary['totalDiscount'];
        $totalProfit = $summary['totalProfit'];
        $totalExpenses = $summary['totalExpenses'];
        $netProfit = $summary['netProfit'];

        $cashiers = User::orderBy('name')->get();

        return view('reports.index', compact(
            'transactions',
            'startDate',
            'endDate',
            'paymentMethod',
            'userId',
            'totalRevenue',
            'totalTransactions',
            'totalDiscount',
            'totalProfit',
            'totalExpenses',
            'netProfit',
            'cashiers',
            'cafeBreakdown'
        ));
    }

    public function reconciliation(Request $request)
    {
        $date = $request->input('date', now()->today()->format('Y-m-d'));
        $userId = $request->input('user_id');

        $query = Transaction::with('user')
            ->paid()
            ->whereDate('paid_at', $date);

        if ($userId) {
            $query->where('app_user_id', $userId);
        }

        $transactions = $query->latest('paid_at')->get();

        $totalCash = $transactions->where('payment_method', 'cash')->sum('final_amount');
        $totalQris = $transactions->where('payment_method', 'qris')->sum('final_amount');
        $totalRevenue = $totalCash + $totalQris;
        $totalTrxCount = $transactions->count();

        $cashiers = User::orderBy('name')->get();

        return view('reports.reconciliation', compact(
            'date',
            'userId',
            'transactions',
            'totalCash',
            'totalQris',
            'totalRevenue',
            'totalTrxCount',
            'cashiers'
        ));
    }

    public function exportCsv(Request $request, SalesReportService $salesReport)
    {
        $startDate = $request->input('start_date', now()->today()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->today()->format('Y-m-d'));
        $paymentMethod = $request->input('payment_method');
        $userId = $request->input('user_id');

        $transactions = $salesReport
            ->transactionsQuery($startDate, $endDate, $paymentMethod, $userId)
            ->get();

        $filename = "Laporan_Penjualan_{$startDate}_sampai_{$endDate}.csv";

        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['No Invoice', 'Tanggal & Waktu', 'No Antrean', 'Tipe Pesanan', 'Meja / Platform', 'Nama Pelanggan', 'Kasir', 'Metode Bayar', 'Subtotal (Rp)', 'Diskon (Rp)', 'Total Akhir (Rp)', 'Bayar (Rp)', 'Kembali (Rp)']);

            // Neutralise spreadsheet formulas in free-text fields (CSV injection).
            $safe = fn ($v) => is_string($v) && preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;

            foreach ($transactions as $trx) {
                fputcsv($file, [
                    $trx->invoice_number,
                    ($trx->paid_at ?? $trx->created_at)->format('d/m/Y H:i:s'),
                    $trx->queue_number,
                    $trx->orderTypeLabel(),
                    $safe($trx->table_name ?? $trx->online_platform ?? ''),
                    $safe($trx->customer_name ?? ''),
                    $safe($trx->user->name ?? '-'),
                    strtoupper($trx->payment_method),
                    $trx->total_amount,
                    $trx->discount_amount,
                    $trx->final_amount,
                    $trx->paid_amount,
                    $trx->change_amount,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request, SalesReportService $salesReport)
    {
        $startDate = $request->input('start_date', now()->today()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->today()->format('Y-m-d'));
        $paymentMethod = $request->input('payment_method');
        $userId = $request->input('user_id');

        $transactions = $salesReport
            ->transactionsQuery($startDate, $endDate, $paymentMethod, $userId)
            ->get();

        $summary = $salesReport->summary($startDate, $endDate, $paymentMethod, $userId);
        $totalRevenue = $summary['totalRevenue'];
        $totalTransactions = $summary['totalTransactions'];
        $totalDiscount = $summary['totalDiscount'];
        $totalProfit = $summary['totalProfit'];
        $totalExpenses = $summary['totalExpenses'];
        $netProfit = $summary['netProfit'];

        $data = compact('transactions', 'startDate', 'endDate', 'totalRevenue', 'totalTransactions', 'totalDiscount', 'totalProfit', 'totalExpenses', 'netProfit');

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf_sales', $data)->setPaper('a4', 'portrait');
            return $pdf->download("Laporan_Penjualan_{$startDate}_sampai_{$endDate}.pdf");
        }

        return view('reports.pdf_sales', $data);
    }

    public function exportShiftPdf(\App\Models\Shift $shift)
    {
        // Cegah IDOR: hanya pemilik shift atau pemegang izin laporan keuangan
        // (owner/admin) yang boleh mengekspor PDF shift.
        abort_unless(
            $shift->app_user_id === auth()->id() || auth()->user()->can('view-financial-reports'),
            403
        );

        $shift->load('user');
        $data = compact('shift');

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.pdf_shift', $data)->setPaper('a4', 'portrait');
            return $pdf->download("Laporan_Shift_{$shift->id}.pdf");
        }

        return view('reports.pdf_shift', $data);
    }
}
