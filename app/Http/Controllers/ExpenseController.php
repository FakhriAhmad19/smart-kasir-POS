<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('user');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('expense_date', [$request->start_date, $request->end_date]);
        } else {
            // Default: current month
            $query->whereMonth('expense_date', now()->month)
                  ->whereYear('expense_date', now()->year);
        }

        $expenses = $query->latest('expense_date')->paginate(15);
        $totalExpenses = $query->sum('amount');

        $categories = [
            'Listrik & Air',
            'Sewa Tempat',
            'Gaji Karyawan',
            'Perlengkapan Toko',
            'Pemasaran & Promosi',
            'Operasional Lainnya',
        ];

        return view('expenses.index', compact('expenses', 'totalExpenses', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        Expense::create([
            'app_user_id' => auth()->id(),
            'category' => $request->category,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Beban operasional berhasil dicatat!');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Catatan beban operasional telah dihapus.');
    }
}
