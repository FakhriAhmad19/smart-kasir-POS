<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    public function index(Request $request)
    {
        $shifts = Shift::with('user')->latest()->paginate(15);
        $activeShift = Shift::where('app_user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        return view('shifts.index', compact('shifts', 'activeShift'));
    }

    public function openForm()
    {
        $activeShift = Shift::where('app_user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        if ($activeShift) {
            return redirect()->route('shifts.index')->with('warning', 'Anda sudah memiliki shift yang aktif!');
        }

        return view('shifts.open');
    }

    public function open(Request $request)
    {
        $request->validate([
            'starting_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $activeShift = Shift::where('app_user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        if ($activeShift) {
            return redirect()->route('shifts.index')->with('warning', 'Anda sudah memiliki shift aktif!');
        }

        Shift::create([
            'app_user_id' => auth()->id(),
            'start_time' => now(),
            'starting_cash' => $request->starting_cash,
            'status' => 'open',
            'notes' => $request->notes,
        ]);

        return redirect()->route('pos.index')->with('success', 'Shift kasir berhasil dibuka dengan modal laci Rp' . number_format($request->starting_cash, 0, ',', '.'));
    }

    public function closeForm()
    {
        $activeShift = Shift::where('app_user_id', auth()->id())
            ->where('status', 'open')
            ->first();

        if (!$activeShift) {
            return redirect()->route('shifts.index')->with('error', 'Tidak ada shift aktif yang perlu ditutup.');
        }

        // Calculate sales recorded against this specific shift
        $cashSales = Transaction::where('app_shift_id', $activeShift->id)
            ->where('payment_method', 'cash')
            ->sum('final_amount');

        $qrisSales = Transaction::where('app_shift_id', $activeShift->id)
            ->where('payment_method', 'qris')
            ->sum('final_amount');

        $expectedEndingCash = $activeShift->starting_cash + $cashSales;

        return view('shifts.close', compact('activeShift', 'cashSales', 'qrisSales', 'expectedEndingCash'));
    }

    public function close(Request $request)
    {
        $request->validate([
            'actual_ending_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $activeShift = Shift::where('app_user_id', auth()->id())
            ->where('status', 'open')
            ->firstOrFail();

        DB::transaction(function () use ($activeShift, $request) {
            $cashSales = Transaction::where('app_shift_id', $activeShift->id)
                ->where('payment_method', 'cash')
                ->sum('final_amount');

            $qrisSales = Transaction::where('app_shift_id', $activeShift->id)
                ->where('payment_method', 'qris')
                ->sum('final_amount');

            $expectedEndingCash = $activeShift->starting_cash + $cashSales;
            $actualEndingCash = (float) $request->actual_ending_cash;
            $difference = $actualEndingCash - $expectedEndingCash;

            $activeShift->update([
                'end_time' => now(),
                'total_cash_sales' => $cashSales,
                'total_qris_sales' => $qrisSales,
                'expected_ending_cash' => $expectedEndingCash,
                'actual_ending_cash' => $actualEndingCash,
                'cash_difference' => $difference,
                'status' => 'closed',
                'notes' => $request->notes ? ($activeShift->notes . ' | ' . $request->notes) : $activeShift->notes,
            ]);
        });

        return redirect()->route('shifts.index')->with('success', 'Shift kasir berhasil ditutup dan direkonsiliasi!');
    }
}
