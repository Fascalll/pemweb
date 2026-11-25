<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransaksiController extends Controller
{
    public function index(): View
    {
        $hasilAgregasi = DB::table('transaksis')->whereYear('tanggal', 2026)
            ->selectRaw('MONTH(tanggal) as bulan, SUM(total) as total_transaksi')
            ->groupByRaw('MONTH(tanggal)')->pluck('total_transaksi', 'bulan');

        $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

        $agregasiBulanan = collect($namaBulan)->map(function ($nama, $nomor) use ($hasilAgregasi) {
            return [
                'bulan' => $nama,
                'total' => $hasilAgregasi->get($nomor, 0),
            ];
        });

        $transaksi = DB::table('transaksis')->whereYear('tanggal', 2026)
            ->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();

        return view('transaksi.index', compact('transaksi', 'hasilAgregasi', 'namaBulan', 'agregasiBulanan'));

    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_transaksi' => 'required|date',
            'jumlah_penjualan' => 'required|integer|min:1',
        ],

            [
                'tanggal_transaksi.required' => 'Tanggal transaksi harus diisi.',
                'tanggal_transaksi.date' => 'Tanggal transaksi harus berupa tanggal yang valid.',
                'jumlah_penjualan.required' => 'Jumlah jersey harus diisi.',
                'jumlah_penjualan.integer' => 'Jumlah jersey harus berupa angka.',
                'jumlah_penjualan.min' => 'Jumlah jersey harus lebih besar dari 0.',
            ]
        );

        $data = [
            'tanggal' => $validated['tanggal_transaksi'],
            'jumlah' => $validated['jumlah_penjualan'],
            'total' => $validated['jumlah_penjualan'],
        ];

        DB::table('transaksis')->insert($data);

        return redirect()->back()->with('success', 'Data transaksi berhasil ditambahkan.');
    }
}
