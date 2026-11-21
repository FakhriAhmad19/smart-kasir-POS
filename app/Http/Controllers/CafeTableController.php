<?php

namespace App\Http\Controllers;

use App\Models\CafeTable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CafeTableController extends Controller
{
    public function index()
    {
        $tables = CafeTable::withCount('transactions')->ordered()->get();
        $areas = $tables->pluck('area')->filter()->unique()->values();

        return view('tables.index', compact('tables', 'areas'));
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());
        CafeTable::create($this->payload($request));

        return redirect()->route('tables.index')->with('success', 'Meja berhasil ditambahkan.');
    }

    /**
     * Quickly create a numbered range, e.g. "Meja 1" … "Meja 10".
     */
    public function bulkStore(Request $request)
    {
        $request->validate([
            'prefix' => 'required|string|max:40',
            'from' => 'required|integer|min:1|max:999',
            'to' => 'required|integer|gte:from|max:999',
            'area' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1|max:50',
        ]);

        if ($request->to - $request->from >= 100) {
            return back()->withErrors(['to' => 'Maksimal 100 meja sekali tambah.']);
        }

        $created = 0;
        for ($n = (int) $request->from; $n <= (int) $request->to; $n++) {
            $table = CafeTable::firstOrCreate(
                ['name' => trim($request->prefix) . ' ' . $n],
                ['area' => $request->area, 'capacity' => $request->capacity, 'sort_order' => $n]
            );
            $created += $table->wasRecentlyCreated ? 1 : 0;
        }

        return redirect()->route('tables.index')->with('success', "{$created} meja berhasil ditambahkan.");
    }

    public function update(Request $request, CafeTable $table)
    {
        $request->validate($this->rules($table));
        $table->update($this->payload($request));

        return redirect()->route('tables.index')->with('success', 'Meja berhasil diperbarui.');
    }

    public function destroy(CafeTable $table)
    {
        // Transactions keep a snapshot of the table name, so history is safe;
        // still, deactivate instead of delete when the table has history.
        if ($table->transactions()->exists()) {
            $table->update(['is_active' => false]);

            return redirect()->route('tables.index')->with('success', 'Meja dinonaktifkan karena memiliki riwayat transaksi.');
        }

        $table->delete();

        return redirect()->route('tables.index')->with('success', 'Meja berhasil dihapus.');
    }

    private function rules(?CafeTable $table = null): array
    {
        return [
            'name' => ['required', 'string', 'max:50', Rule::unique('app_tables', 'name')->ignore($table?->id)],
            'area' => 'nullable|string|max:50',
            'capacity' => 'nullable|integer|min:1|max:50',
            'sort_order' => 'nullable|integer|min:0|max:9999',
        ];
    }

    private function payload(Request $request): array
    {
        return [
            'name' => trim($request->name),
            'area' => $request->filled('area') ? trim($request->area) : null,
            'capacity' => $request->capacity ?: null,
            'sort_order' => (int) ($request->sort_order ?? 0),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
