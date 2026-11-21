<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request, string $station)
    {
        abort_unless(in_array($station, ['barista', 'kitchen'], true), 404);

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

}
