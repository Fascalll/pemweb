<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index()
    {
        $hasilAgregasi = DB::table('transaksis')->whereBetween('tanggal', [now()->subDays(30), now()])
        ->selectRaw('MONTH(tanggal) as bulan, SUM(total) as total_transaksi')
        ->groupByRaw('MONTH(tanggal)')->pluck('total_transaksi', 'bulan');

        $namaBulan = [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];

        $agregasiBulanan = collect($namaBulan)->map(function ($nama, $nomor) use ($hasilAgregasi) {
            return [
                'bulan' => $nama,
                'total' => $hasilAgregasi->get($nomor, 0)
            ];
        });

        $transaksi = DB::table('transaksis')->whereBetween('tanggal', [now()->subDays(30), now()])
        ->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();

        return view('transaksi.index', compact('transaksi', 'hasilAgregasi', 'namaBulan', 'agregasiBulanan'));

    }

    public function store()
    {
        $data = request()->validate([
            'tanggal' => 'required|date',
            'jumlah' => 'required|integer|min:1',
            'total' => 'required|integer|min:1',
        ],
        
        [
            'tanggal.required' => 'Tanggal transaksi harus diisi.',
            'tanggal.date' => 'Tanggal transaksi harus berupa tanggal yang valid.',
            'jumlah.required' => 'Jumlah barang harus diisi.',
            'jumlah.integer' => 'Jumlah barang harus berupa angka.',
            'jumlah.min' => 'Jumlah barang harus lebih besar dari 0.',
            'total.required' => 'Total transaksi harus diisi.',
            'total.integer' => 'Total transaksi harus berupa angka.',
            'total.min' => 'Total transaksi harus lebih besar dari 0.',
        ]
        );

        DB::table('transaksis')->insert($data);

        return redirect()->back()->with('success', 'Data transaksi berhasil ditambahkan.');
    }
}
