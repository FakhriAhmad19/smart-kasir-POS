<?php

namespace App\Http\Controllers;

use App\Exceptions\OrderException;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RefundController extends Controller
{
    public function refund(Request $request, Transaction $transaction, OrderService $orders)
    {
        $request->validate([
            'supervisor_pin' => 'required|digits_between:4,8',
            'reason' => 'required|string|min:3|max:255',
        ]);
        $this->authorizePin($request->supervisor_pin);

        try {
            $orders->refund($transaction, auth()->id(), trim($request->reason));
        } catch (OrderException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'Transaksi berhasil direfund dan stok dikembalikan.');
    }

    public function voidItem(Request $request, TransactionDetail $detail, OrderService $orders)
    {
        $request->validate([
            'supervisor_pin' => 'required|digits_between:4,8',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|min:3|max:255',
        ]);
        $this->authorizePin($request->supervisor_pin);

        try {
            $orders->voidItem($detail, (int) $request->quantity, auth()->id(), trim($request->reason));
        } catch (OrderException $e) {
            return back()->with('error', $e->getMessage());
        }
        return back()->with('success', 'Item berhasil di-void dan stok dikembalikan.');
    }

    private function authorizePin(string $pin): void
    {
        $hash = Setting::get('supervisor_pin_hash');
        abort_unless($hash && Hash::check($pin, $hash), 403, 'PIN supervisor salah atau belum diatur.');
    }
}
