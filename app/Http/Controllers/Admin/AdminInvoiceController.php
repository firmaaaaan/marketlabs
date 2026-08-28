<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class AdminInvoiceController extends Controller
{
    public function index()
    {
        return view('admin.settings.invoice', [
            'company_name' => Setting::get('invoice_company_name', 'MarketLabs'),
            'company_subtitle' => Setting::get('invoice_company_subtitle', 'Laboratorium Riset & Pengujian'),
            'company_tagline' => Setting::get('invoice_company_tagline', 'by UPT Laboratorium Terpadu UNISA Yogyakarta'),
            'company_address' => Setting::get('invoice_company_address', 'Jln. Teknologi No. 1, Kota Sains'),
            'company_phone' => Setting::get('invoice_company_phone', ''),
            'company_email' => Setting::get('invoice_company_email', ''),
            'company_website' => Setting::get('invoice_company_website', ''),
            'footer_text' => Setting::get('invoice_footer_text', 'Terima kasih telah menggunakan layanan MarketLabs. Invoice ini sah tanpa tanda tangan.'),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'company_subtitle' => ['nullable', 'string', 'max:255'],
            'company_tagline' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'string', 'max:255'],
            'company_website' => ['nullable', 'string', 'max:255'],
            'footer_text' => ['nullable', 'string', 'max:500'],
        ]);

        Setting::set('invoice_company_name', $validated['company_name']);
        Setting::set('invoice_company_subtitle', trim($validated['company_subtitle'] ?? ''));
        Setting::set('invoice_company_tagline', trim($validated['company_tagline'] ?? ''));
        Setting::set('invoice_company_address', trim($validated['company_address'] ?? ''));
        Setting::set('invoice_company_phone', trim($validated['company_phone'] ?? ''));
        Setting::set('invoice_company_email', trim($validated['company_email'] ?? ''));
        Setting::set('invoice_company_website', trim($validated['company_website'] ?? ''));
        Setting::set('invoice_footer_text', trim($validated['footer_text'] ?? ''));

        return redirect()->route('admin.invoice.index')
            ->with('success', 'Pengaturan invoice berhasil diperbarui.');
    }
}
