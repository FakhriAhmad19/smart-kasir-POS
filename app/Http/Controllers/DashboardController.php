<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->today();
        $thisMonth = now()->firstOfMonth();

        // Omset & Transaction Count
        $todayRevenue = Transaction::whereDate('created_at', $today)->sum('final_amount');
        $todayTransactionsCount = Transaction::whereDate('created_at', $today)->count();
        $monthlyRevenue = Transaction::where('created_at', '>=', $thisMonth)->sum('final_amount');

        // Payment Method Split Today
        $todayCashRevenue = Transaction::whereDate('created_at', $today)->where('payment_method', 'cash')->sum('final_amount');
        $todayQrisRevenue = Transaction::whereDate('created_at', $today)->where('payment_method', 'qris')->sum('final_amount');

        // Gross Profit Calculation
        $todayGrossProfit = TransactionDetail::whereHas('transaction', function ($q) use ($today) {
            $q->whereDate('created_at', $today);
        })->selectRaw('SUM((selling_price - purchase_price) * quantity) as gross_profit')->value('gross_profit') ?? 0;

        // Expenses Today & Net Profit
        $todayExpenses = Expense::whereDate('expense_date', $today)->sum('amount');
        $todayNetProfit = $todayGrossProfit - $todayExpenses;

        // Low Stock Alert Items
        $lowStockProducts = Product::with('category')
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock', 'asc')
            ->get();

        // Top 5 Best Selling Products
        $topSellingProducts = TransactionDetail::select('app_product_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(subtotal) as total_sales'))
            ->with('product.category')
            ->groupBy('app_product_id')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        // Recent 5 Transactions
        $recentTransactions = Transaction::with('user')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'todayRevenue',
            'todayTransactionsCount',
            'monthlyRevenue',
            'todayCashRevenue',
            'todayQrisRevenue',
            'todayGrossProfit',
            'todayExpenses',
            'todayNetProfit',
            'lowStockProducts',
            'topSellingProducts',
            'recentTransactions'
        ));
    }
}
