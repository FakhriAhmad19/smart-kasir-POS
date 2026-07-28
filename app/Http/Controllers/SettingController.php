<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'store_name' => Setting::get('store_name', 'SMART KASIR UMKM'),
            'store_address' => Setting::get('store_address', 'Jl. Raya Utama No. 88, Indonesia'),
            'store_phone' => Setting::get('store_phone', '0812-3456-7890'),
            'store_email' => Setting::get('store_email', 'info@smartkasir.com'),
            'receipt_footer_note' => Setting::get('receipt_footer_note', "*** TERIMA KASIH ***\nBarang yang sudah dibeli tidak dapat ditukar/dikembalikan."),
        ];

        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'store_name' => 'required|string|max:255',
            'store_address' => 'required|string|max:500',
            'store_phone' => 'required|string|max:50',
            'store_email' => 'nullable|email|max:255',
            'receipt_footer_note' => 'nullable|string|max:500',
        ]);

        Setting::set('store_name', $request->store_name);
        Setting::set('store_address', $request->store_address);
        Setting::set('store_phone', $request->store_phone);
        Setting::set('store_email', $request->store_email);
        Setting::set('receipt_footer_note', $request->receipt_footer_note);

        return redirect()->route('settings.index')->with('success', 'Pengaturan profil toko & struk berhasil diperbarui!');
    }
}
