<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_valid_payload_creates_transaction_and_redirects(): void
    {
        $response = $this->from('/')->post('/transaksi', [
            'tanggal_transaksi' => '2026-11-25',
            'jumlah_penjualan' => 5,
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('success', 'Data transaksi berhasil ditambahkan.');

        $this->assertDatabaseHas('transaksis', [
            'tanggal' => '2026-11-25',
            'jumlah' => 5,
            'total' => 5,
        ]);
    }

    public function test_transaction_from_earlier_in_2026_is_visible_on_dashboard(): void
    {
        $this->post('/transaksi', [
            'tanggal_transaksi' => '2026-10-15',
            'jumlah_penjualan' => 10,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('15-10-2026');
        $response->assertSee('10');
    }

    public function test_empty_payload_returns_validation_errors_without_creating_transaction(): void
    {
        $response = $this->from('/')->post('/transaksi', []);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors([
            'tanggal_transaksi',
            'jumlah_penjualan',
        ]);
        $this->assertDatabaseCount('transaksis', 0);
    }
}
