<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request, string $station)
    {
        abort_unless(in_array($station, ['barista', 'kitchen'], true), 404);
        abort_unless(auth()->user()->can("view-{$station}-tickets"), 403);

        $tickets = Transaction::paid()
            ->where('paid_at', '>=', now()->subDay())
            ->whereHas('details', fn ($q) => $q->where('station', $station))
            ->with(['details' => fn ($q) => $q->where('station', $station)->with('product')])
            ->orderBy('paid_at')
            ->get();

        return view('tickets.index', [
            'station' => $station,
            'stationLabel' => $station === 'barista' ? 'Barista' : 'Kitchen',
            'tickets' => $tickets,
        ]);
    }

    public function updateStatus(Request $request, string $station, TransactionDetail $detail)
    {
        abort_unless(in_array($station, ['barista', 'kitchen'], true), 404);
        abort_unless(auth()->user()->can("view-{$station}-tickets"), 403);

        $detail->load('transaction');
        abort_unless(
            $detail->station === $station && $detail->transaction?->status === Transaction::STATUS_PAID,
            404
        );

        $request->validate([
            'ticket_status' => 'required|in:' . implode(',', array_keys(TransactionDetail::TICKET_STATUSES)),
        ]);

        $detail->update(['ticket_status' => $request->ticket_status]);

        return back()->with('success', 'Status tiket diperbarui.');
    }

}
