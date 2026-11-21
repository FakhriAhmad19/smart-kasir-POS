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
            ->paid()
            ->whereDate('paid_at', '>=', $startDate)
            ->whereDate('paid_at', '<=', $endDate)
            ->latest('paid_at');

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
        $summaryQuery = Transaction::paid()
            ->whereDate('paid_at', '>=', $startDate)
            ->whereDate('paid_at', '<=', $endDate);
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

    /** Cafe-specific operational breakdowns from the same paid filter set. */
    public function cafeBreakdown(
        string $startDate,
        string $endDate,
        ?string $paymentMethod = null,
        ?string $userId = null
    ): array {
        $transactions = $this->transactionsQuery($startDate, $endDate, $paymentMethod, $userId)->get();
        $details = $transactions->flatMap->details;

        $hourlySales = $transactions->groupBy(fn ($trx) => $trx->paid_at?->format('H:00') ?? '-')->map(fn ($group) => [
            'transactions' => $group->count(),
            'revenue' => $group->sum('final_amount'),
        ])->sortKeys();

        $orderTypes = $transactions->groupBy('order_type')->map(fn ($group) => [
            'label' => Transaction::ORDER_TYPES[$group->first()->order_type] ?? $group->first()->order_type,
            'transactions' => $group->count(),
            'revenue' => $group->sum('final_amount'),
        ]);

        $topProducts = $details->filter(fn ($detail) => $detail->quantity > 0)->groupBy(fn ($detail) => $detail->product->name ?? 'Produk')->map(fn ($group) => [
            'quantity' => $group->sum('quantity'),
            'revenue' => $group->sum('subtotal'),
        ])->sortByDesc('quantity')->take(10);

        $modifierCounts = [];
        foreach ($details as $detail) {
            foreach ($detail->modifiers ?? [] as $modifier) {
                $name = ($modifier['group'] ?? 'Modifier') . ': ' . ($modifier['option'] ?? '-');
                $modifierCounts[$name] = ($modifierCounts[$name] ?? 0) + (int) $detail->quantity;
            }
        }
        arsort($modifierCounts);

        return [
            'cupsSold' => $details->sum('quantity'),
            'hourlySales' => $hourlySales,
            'orderTypes' => $orderTypes,
            'topProducts' => $topProducts,
            'topModifiers' => collect($modifierCounts)->take(10),
        ];
    }
}
