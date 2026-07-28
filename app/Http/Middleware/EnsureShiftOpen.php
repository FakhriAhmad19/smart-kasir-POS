<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Shift;

class EnsureShiftOpen
{
    /**
     * Handle an incoming request.
     * Redirect user to open shift form if no active shift found.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        $activeShift = Shift::where('app_user_id', $user->id)
            ->where('status', 'open')
            ->first();

        if (!$activeShift) {
            return redirect()->route('shifts.open_form')->with('warning', 'Buka shift kasir terlebih dahulu sebelum melakukan transaksi.');
        }

        return $next($request);
    }
}
