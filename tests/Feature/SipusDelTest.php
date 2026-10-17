<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SipusDelTest extends TestCase
{
    use RefreshDatabase;

    protected User $pustakawan;

    protected function setUp(): void
    {
        parent::setUp();

        // Siapkan akun pustakawan standar untuk pengujian
        $this->pustakawan = User::create([
            'name' => 'Pustakawan Del',
            'email' => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_root_redirects_to_buku_index(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('buku.index'));
    }

    public function test_can_view_buku_index_with_statistics_cards(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'KAT-INF',
            'nama_kategori' => 'Informatika',
        ]);

        $buku = Buku::create([
            'isbn' => '978-0132350884',
            'judul' => 'Clean Code',
            'penulis' => 'Robert C. Martin',
            'penerbit' => 'Prentice Hall',
            'tahun_terbit' => 2008,
            'kategori_id' => $kategori->id,
            'stok' => 10,
            'sinopsis' => 'Panduan penulisan kode bersih.',
        ]);

        $response = $this->get(route('buku.index'));
        $response->assertStatus(200);
        $response->assertSee('Clean Code');
        $response->assertSee('Informatika');
        $response->assertSee('Total Judul Buku');
    }

    public function test_auth_login_with_valid_credentials_and_cookie(): void
    {
        $response = $this->post(route('login.post'), [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('buku.index'));
        $this->assertAuthenticatedAs($this->pustakawan);
        $response->assertCookie('del_last_user_email', 'pustakawan@del.ac.id');
    }

    public function test_auth_login_fails_with_invalid_credentials(): void
    {
        $response = $this->post(route('login.post'), [
            'email' => 'pustakawan@del.ac.id',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_auth_logout_clears_session(): void
    {
        $this->actingAs($this->pustakawan);

        $response = $this->post(route('logout'));
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_unauthenticated_user_cannot_access_mutation_routes(): void
    {
        // Akses form peminjaman tanpa login harus dialihkan ke login
        $response = $this->get(route('peminjaman.create'));
        $response->assertRedirect(route('login'));

        // Akses form tambah buku tanpa login harus dialihkan ke login
        $resBuku = $this->get(route('buku.create'));
        $resBuku->assertRedirect(route('login'));
    }

    public function test_peminjaman_buku_acid_success_decrements_stock(): void
    {
        $this->actingAs($this->pustakawan);

        $kategori = Kategori::create([
            'kode_kategori' => 'KAT-RPL',
            'nama_kategori' => 'Rekayasa Perangkat Lunak',
        ]);

        $buku = Buku::create([
            'isbn' => '978-0134757599',
            'judul' => 'Refactoring',
            'penulis' => 'Martin Fowler',
            'penerbit' => 'Addison-Wesley',
            'tahun_terbit' => 2018,
            'kategori_id' => $kategori->id,
            'stok' => 5,
        ]);

        $payload = [
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24018',
            'nama_peminjam' => 'Kelvin Marpaung',
            'durasi_hari' => 7,
        ];

        $response = $this->post(route('peminjaman.store'), $payload);

        $response->assertRedirect(route('peminjaman.index'));
        $response->assertSessionHas('success');

        // Pastikan stok berkurang menjadi 4 secara atomik
        $this->assertEquals(4, $buku->fresh()->stok);

        // Pastikan data tersimpan di tabel peminjamans
        $this->assertDatabaseHas('peminjamans', [
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24018',
            'status' => 'dipinjam',
        ]);
    }

    public function test_peminjaman_buku_rejected_when_stock_is_zero(): void
    {
        $this->actingAs($this->pustakawan);

        $kategori = Kategori::create([
            'kode_kategori' => 'KAT-AI',
            'nama_kategori' => 'Kecerdasan Buatan',
        ]);

        $bukuStokNol = Buku::create([
            'isbn' => '978-0131429383',
            'judul' => 'Buku Habis',
            'penulis' => 'Penulis Uji',
            'penerbit' => 'Penerbit Uji',
            'tahun_terbit' => 2020,
            'kategori_id' => $kategori->id,
            'stok' => 0,
        ]);

        $payload = [
            'buku_id' => $bukuStokNol->id,
            'nim_peminjam' => '12S24099',
            'nama_peminjam' => 'Siswa Test',
            'durasi_hari' => 7,
        ];

        $response = $this->post(route('peminjaman.store'), $payload);

        // Harus ditolak dengan pesan error
        $response->assertSessionHas('error');
        $this->assertEquals(0, $bukuStokNol->fresh()->stok);
        $this->assertDatabaseMissing('peminjamans', [
            'buku_id' => $bukuStokNol->id,
            'nim_peminjam' => '12S24099',
        ]);
    }

    public function test_peminjaman_buku_rejected_if_same_nim_has_active_borrow(): void
    {
        $this->actingAs($this->pustakawan);

        $kategori = Kategori::create([
            'kode_kategori' => 'KAT-CYB',
            'nama_kategori' => 'Keamanan Siber',
        ]);

        $buku = Buku::create([
            'isbn' => '978-0133594140',
            'judul' => 'Computer Networking',
            'penulis' => 'James Kurose',
            'penerbit' => 'Pearson',
            'tahun_terbit' => 2021,
            'kategori_id' => $kategori->id,
            'stok' => 10,
        ]);

        // Pinjaman pertama berhasil
        $this->post(route('peminjaman.store'), [
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24018',
            'nama_peminjam' => 'Kelvin Marpaung',
            'durasi_hari' => 7,
        ]);

        // Pinjaman kedua dengan NIM yang sama untuk buku yang sama harus ditolak
        $response = $this->post(route('peminjaman.store'), [
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24018',
            'nama_peminjam' => 'Kelvin Marpaung',
            'durasi_hari' => 7,
        ]);

        $response->assertSessionHas('error');
        // Stok hanya berkurang 1 kali (menjadi 9)
        $this->assertEquals(9, $buku->fresh()->stok);
    }

    public function test_pengembalian_buku_acid_success_increments_stock(): void
    {
        $this->actingAs($this->pustakawan);

        $kategori = Kategori::create([
            'kode_kategori' => 'KAT-MAT',
            'nama_kategori' => 'Matematika Diskrit',
        ]);

        $buku = Buku::create([
            'isbn' => '978-0073383095',
            'judul' => 'Discrete Mathematics',
            'penulis' => 'Kenneth H. Rosen',
            'penerbit' => 'McGraw-Hill',
            'tahun_terbit' => 2018,
            'kategori_id' => $kategori->id,
            'stok' => 2,
        ]);

        $peminjaman = Peminjaman::create([
            'kode_pinjam' => 'PINJAM-TEST-0001',
            'buku_id' => $buku->id,
            'nim_peminjam' => '12S24001',
            'nama_peminjam' => 'Andi Siregar',
            'tanggal_pinjam' => now()->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
            'tanggal_kembali' => null,
            'status' => 'dipinjam',
        ]);

        // Eksekusi pengembalian buku
        $response = $this->patch(route('peminjaman.kembalikan', $peminjaman));

        $response->assertRedirect(route('peminjaman.index'));
        $response->assertSessionHas('success');

        // Stok buku bertambah menjadi 3
        $this->assertEquals(3, $buku->fresh()->stok);

        // Status berubah menjadi dikembalikan
        $this->assertEquals('dikembalikan', $peminjaman->fresh()->status);
        $this->assertNotNull($peminjaman->fresh()->tanggal_kembali);
    }
}
