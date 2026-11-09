<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SipusDelApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $pustakawan;
    protected Kategori $kategori;
    protected Buku $bukuTersedia;
    protected Buku $bukuHabis;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pustakawan = User::create([
            'name' => 'Pustakawan Del',
            'email' => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);

        $this->kategori = Kategori::create([
            'kode_kategori' => 'KAT-SI',
            'nama_kategori' => 'Sistem Informasi',
        ]);

        $this->bukuTersedia = Buku::create([
            'isbn' => '978-0132350884',
            'judul' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
            'penulis' => 'Robert C. Martin',
            'penerbit' => 'Prentice Hall',
            'tahun_terbit' => 2008,
            'kategori_id' => $this->kategori->id,
            'stok' => 5,
            'sinopsis' => 'Panduan komprehensif penulisan kode bersih.',
        ]);

        $this->bukuHabis = Buku::create([
            'isbn' => '978-0131429383',
            'judul' => 'Modern Operating Systems (Stok Habis)',
            'penulis' => 'Andrew S. Tanenbaum',
            'penerbit' => 'Pearson Education',
            'tahun_terbit' => 2015,
            'kategori_id' => $this->kategori->id,
            'stok' => 0,
            'sinopsis' => 'Buku stok habis untuk pengujian penolakan 422.',
        ]);
    }

    public function test_soap_wsdl_returns_xml_contract(): void
    {
        $response = $this->get('/soap/sipus.wsdl');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');
        $this->assertStringContainsString('<definitions name="SIPUSSoapService"', $response->getContent());
        $this->assertStringContainsString('CheckClearanceStatus', $response->getContent());
    }

    public function test_soap_check_clearance_status_cleared(): void
    {
        // Catat riwayat peminjaman yang sudah dikembalikan
        Peminjaman::create([
            'kode_pinjam' => 'PINJ-20261001-0001',
            'buku_id' => $this->bukuTersedia->id,
            'nim_peminjam' => '12S24018',
            'nama_peminjam' => 'Kelvin Yohanes Putra',
            'tanggal_pinjam' => '2026-10-01',
            'tanggal_jatuh_tempo' => '2026-10-08',
            'tanggal_kembali' => '2026-10-05',
            'status' => 'dikembalikan',
        ]);

        $soapXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:m="http://sipus.del.ac.id/soap/library">
    <soap:Body>
        <m:CheckClearanceStatusRequest>
            <m:nim_peminjam>12S24018</m:nim_peminjam>
        </m:CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], $soapXml);

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');
        $this->assertStringContainsString('<m:is_cleared>true</m:is_cleared>', $response->getContent());
        $this->assertStringContainsString('<m:active_loans>0</m:active_loans>', $response->getContent());
    }

    public function test_soap_check_clearance_status_fault_when_nim_not_found(): void
    {
        $soapXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"
               xmlns:m="http://sipus.del.ac.id/soap/library">
    <soap:Body>
        <m:CheckClearanceStatusRequest>
            <m:nim_peminjam>00000000</m:nim_peminjam>
        </m:CheckClearanceStatusRequest>
    </soap:Body>
</soap:Envelope>
XML;

        $response = $this->call('POST', '/soap/sipus', [], [], [], [
            'CONTENT_TYPE' => 'text/xml; charset=utf-8',
        ], $soapXml);

        $response->assertStatus(500);
        $response->assertHeader('Content-Type', 'text/xml; charset=utf-8');
        $this->assertStringContainsString('<faultcode>soap:Client</faultcode>', $response->getContent());
    }

    public function test_auth_api_login_success_returns_bearer_token(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'password123',
            'device_name' => 'phpunit-client',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'token_type' => 'Bearer',
                    'user' => [
                        'email' => 'pustakawan@del.ac.id',
                    ],
                ],
            ]);

        $this->assertNotEmpty($response->json('data.access_token'));
    }

    public function test_auth_api_login_failed_returns_401_rfc7807(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJson([
                'status' => 401,
                'title' => 'Invalid Credentials',
            ]);
    }

    public function test_buku_api_index_returns_paginated_and_hateoas(): void
    {
        $response = $this->getJson('/api/v1/bukus?per_page=2');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'isbn',
                        'judul',
                        'stok',
                        '_links',
                    ],
                ],
                'meta' => [
                    'current_page',
                    'per_page',
                    'total_items',
                    'total_pages',
                    'links',
                ],
            ]);
    }

    public function test_buku_api_show_not_found_returns_404_rfc7807(): void
    {
        $response = $this->getJson('/api/v1/bukus/99999');

        $response->assertStatus(404)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJson([
                'status' => 404,
                'title' => 'Resource Not Found',
            ]);
    }

    public function test_buku_api_store_requires_auth_returns_401(): void
    {
        $response = $this->postJson('/api/v1/bukus', [
            'isbn' => '978-6020300001',
            'judul' => 'Buku Baru',
        ]);

        $response->assertStatus(401)
            ->assertHeader('Content-Type', 'application/problem+json');
    }

    public function test_buku_api_store_success_returns_201_and_location(): void
    {
        $token = $this->pustakawan->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/bukus', [
                'isbn' => '978-6239999901',
                'judul' => 'Arsitektur REST API & Microservices di Laravel 11',
                'penulis' => 'Chandro Pardede',
                'penerbit' => 'Del Press',
                'tahun_terbit' => 2026,
                'kategori_id' => $this->kategori->id,
                'stok' => 10,
                'sinopsis' => 'Buku rujukan arsitektur REST.',
            ]);

        $response->assertStatus(201)
            ->assertHeader('Location');

        $this->assertDatabaseHas('bukus', [
            'isbn' => '978-6239999901',
        ]);
    }

    public function test_buku_api_patch_vs_put(): void
    {
        $token = $this->pustakawan->createToken('test')->plainTextToken;

        // PATCH: Hanya ubah stok
        $patchRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/bukus/{$this->bukuTersedia->id}", [
                'stok' => 25,
            ]);

        $patchRes->assertStatus(200);
        $this->assertEquals(25, $this->bukuTersedia->fresh()->stok);
        $this->assertEquals('Clean Code: A Handbook of Agile Software Craftsmanship', $this->bukuTersedia->fresh()->judul);

        // PUT: Ganti seluruh atribut
        $putRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->putJson("/api/v1/bukus/{$this->bukuTersedia->id}", [
                'isbn' => '978-0132350884',
                'judul' => 'Clean Code 2nd Edition',
                'penulis' => 'Robert C. Martin',
                'penerbit' => 'Prentice Hall',
                'tahun_terbit' => 2020,
                'kategori_id' => $this->kategori->id,
                'stok' => 30,
            ]);

        $putRes->assertStatus(200);
        $this->assertEquals('Clean Code 2nd Edition', $this->bukuTersedia->fresh()->judul);
        $this->assertNull($this->bukuTersedia->fresh()->sinopsis); // Direset ke null pada PUT murni
    }

    public function test_buku_api_delete_conflict_when_active_loans_returns_409(): void
    {
        $token = $this->pustakawan->createToken('test')->plainTextToken;

        // Pasang pinjaman aktif
        Peminjaman::create([
            'kode_pinjam' => 'PINJ-AKTIF-001',
            'buku_id' => $this->bukuTersedia->id,
            'nim_peminjam' => '12S24099',
            'nama_peminjam' => 'Mahasiswa Aktif',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'status' => 'dipinjam',
        ]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/bukus/{$this->bukuTersedia->id}");

        $response->assertStatus(409)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJson([
                'status' => 409,
                'title' => 'Conflict — Book Has Active Loans',
            ]);
    }

    public function test_peminjaman_api_acid_borrow_and_return(): void
    {
        $token = $this->pustakawan->createToken('test')->plainTextToken;

        // 1. Pinjam Buku Sukses
        $borrowRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id' => $this->bukuTersedia->id,
                'nim_peminjam' => '12S24018',
                'nama_peminjam' => 'Kelvin Yohanes Putra',
                'durasi_hari' => 7,
            ]);

        $borrowRes->assertStatus(201)
            ->assertHeader('Location');

        $peminjamanId = $borrowRes->json('data.id');
        $this->assertEquals(4, $this->bukuTersedia->fresh()->stok);

        // 2. Tolak Pinjam Ganda pada Buku yang Sama
        $dupRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id' => $this->bukuTersedia->id,
                'nim_peminjam' => '12S24018',
                'nama_peminjam' => 'Kelvin Yohanes Putra',
                'durasi_hari' => 7,
            ]);

        $dupRes->assertStatus(409)
            ->assertHeader('Content-Type', 'application/problem+json');

        // 3. Kembalikan Buku Sukses
        $returnRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/peminjamans/{$peminjamanId}/kembalikan");

        $returnRes->assertStatus(200);
        $this->assertEquals(5, $this->bukuTersedia->fresh()->stok);
        $this->assertEquals('dikembalikan', Peminjaman::find($peminjamanId)->status);

        // 4. Tolak Pengembalian Ulang Buku yang Sudah Dikembalikan
        $returnAgainRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->patchJson("/api/v1/peminjamans/{$peminjamanId}/kembalikan");

        $returnAgainRes->assertStatus(409)
            ->assertHeader('Content-Type', 'application/problem+json');
    }

    public function test_peminjaman_api_rejected_when_stock_zero_returns_422(): void
    {
        $token = $this->pustakawan->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/peminjamans', [
                'buku_id' => $this->bukuHabis->id,
                'nim_peminjam' => '12S24020',
                'nama_peminjam' => 'Bona Pandiangan',
                'durasi_hari' => 7,
            ]);

        $response->assertStatus(422)
            ->assertHeader('Content-Type', 'application/problem+json')
            ->assertJson([
                'status' => 422,
                'title' => 'Book Out of Stock',
            ]);
    }

    public function test_buku_referensi_eksternal_consumer_openlibrary(): void
    {
        Http::fake([
            'openlibrary.org/api/books*' => Http::response([
                'ISBN:9780132350884' => [
                    'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                    'publishers' => [['name' => 'Prentice Hall']],
                    'publish_date' => 'August 1, 2008',
                    'number_of_pages' => 464,
                ],
            ], 200),
        ]);

        $response = $this->getJson("/api/v1/bukus/{$this->bukuTersedia->id}/referensi-eksternal");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'isbn' => $this->bukuTersedia->isbn,
                    'sumber' => 'OpenLibrary REST API',
                ],
            ]);
    }
}
