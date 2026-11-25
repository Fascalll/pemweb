<?php

use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\TransaksiController::class, 'index'])->name('transaksi.index');
Route::post('/transaksi', [App\Http\Controllers\TransaksiController::class, 'store'])->name('transaksi.store');
