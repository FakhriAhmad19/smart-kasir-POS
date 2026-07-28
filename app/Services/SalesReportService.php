<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Transaction;
use App\Models\TransactionDetail;

class SalesReportService
{
    /**
     * Build the base query for the sales report transaction list.
     *
     * Mirrors the eager loading, date range, optional filters and ordering
     * previously duplicated across the controller. Callers decide whether to
     * paginate or ->get() the result.
     */
    public function transactionsQuery(
        string $startDate,
        string $endDate,
        ?string $paymentMethod = null,
        ?string $userId = null
    ) {
        $query = Transaction::with(['user', 'details.product'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->latest();

        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        if ($userId) {
            $query->where('app_user_id', $userId);
        }

        return $query;
    }

    /**
     * Calculate the summary figures (revenue, transactions, discount, gross
     * profit, expenses and net profit) for the given filter set.
     *
     * @return array<string, mixed>
     */
    public function summary(
        string $startDate,
        string $endDate,
        ?string $paymentMethod = null,
        ?string $userId = null
    ): array {
        $summaryQuery = Transaction::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate);
        if ($paymentMethod) $summaryQuery->where('payment_method', $paymentMethod);
        if ($userId) $summaryQuery->where('app_user_id', $userId);

        $totalRevenue = $summaryQuery->sum('final_amount');
        $totalTransactions = $summaryQuery->count();
        $totalDiscount = $summaryQuery->sum('discount_amount');

        // Gross Profit Calculation for filtered set
        $transactionIds = $summaryQuery->pluck('id');
        $totalProfit = TransactionDetail::whereIn('app_transaction_id', $transactionIds)
            ->selectRaw('SUM((selling_price - purchase_price) * quantity) as profit')
            ->value('profit') ?? 0;

        // Expenses & Net Profit for date range
        $totalExpenses = Expense::whereBetween('expense_date', [$startDate, $endDate])->sum('amount');
        $netProfit = $totalProfit - $totalExpenses;

        return compact(
            'totalRevenue',
            'totalTransactions',
            'totalDiscount',
            'totalProfit',
            'totalExpenses',
            'netProfit'
        );
    }
}
