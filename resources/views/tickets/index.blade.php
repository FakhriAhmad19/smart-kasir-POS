<x-app-layout>
    <x-slot name="title">Tiket {{ $stationLabel }} - Smart Kasir</x-slot>
    <x-slot name="header">Tiket {{ $stationLabel }}</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h5 class="fw-bold mb-1">Pesanan untuk {{ $stationLabel }}</h5><small class="text-muted">Menampilkan transaksi dibayar dalam 24 jam terakhir.</small></div>
        <div class="btn-group"><a class="btn btn-sm {{ $station === 'barista' ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('tickets.index', 'barista') }}">Barista</a><a class="btn btn-sm {{ $station === 'kitchen' ? 'btn-primary' : 'btn-outline-primary' }}" href="{{ route('tickets.index', 'kitchen') }}">Kitchen</a><button class="btn btn-sm btn-outline-secondary" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i></button></div>
    </div>
    <div class="row g-3">
        @forelse($tickets as $ticket)
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-dark text-white d-flex justify-content-between"><span class="fw-bold">#{{ $ticket->queue_number }} · {{ $ticket->orderTypeLabel() }}</span><small>{{ $ticket->paid_at->format('H:i') }}</small></div>
                    <div class="card-body">
                        <div class="fw-bold mb-2">{{ $ticket->customer_name ?: ($ticket->table_name ?: 'Pelanggan') }}</div>
                        @foreach($ticket->details as $detail)
                            <div class="border-top py-2 {{ $detail->ticket_status === 'ready' ? 'opacity-50' : '' }}">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <span class="fw-bold">{{ $detail->quantity }}× {{ $detail->product->name ?? 'Produk' }}</span>
                                    <span class="badge {{ $detail->ticket_status === 'ready' ? 'bg-success' : ($detail->ticket_status === 'preparing' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                        {{ \App\Models\TransactionDetail::TICKET_STATUSES[$detail->ticket_status] ?? 'Menunggu' }}
                                    </span>
                                </div>
                                @if($detail->modifiers)<small class="text-muted">{{ collect($detail->modifiers)->pluck('option')->join(', ') }}</small>@endif
                                @if($detail->notes)<div class="small text-warning-emphasis fst-italic">* {{ $detail->notes }}</div>@endif
                                <div class="mt-2 d-flex gap-1">
                                    @if($detail->ticket_status === 'pending')
                                        <form method="POST" action="{{ route('tickets.status', [$station, $detail]) }}">@csrf @method('PATCH')<input type="hidden" name="ticket_status" value="preparing"><button class="btn btn-sm btn-outline-warning">Mulai</button></form>
                                    @elseif($detail->ticket_status === 'preparing')
                                        <form method="POST" action="{{ route('tickets.status', [$station, $detail]) }}">@csrf @method('PATCH')<input type="hidden" name="ticket_status" value="ready"><button class="btn btn-sm btn-success">Siap</button></form>
                                    @else
                                        <form method="POST" action="{{ route('tickets.status', [$station, $detail]) }}">@csrf @method('PATCH')<input type="hidden" name="ticket_status" value="pending"><button class="btn btn-sm btn-outline-secondary">Kembalikan</button></form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-light border text-center text-muted py-5">Belum ada tiket {{ strtolower($stationLabel) }}.</div></div>
        @endforelse
    </div>
</x-app-layout>
