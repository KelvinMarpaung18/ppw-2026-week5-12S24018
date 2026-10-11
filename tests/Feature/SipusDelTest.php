<?php

namespace Tests\Feature;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SipusDelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_root_redirects_to_buku_index(): void
    {
        $response = $this->get('/');
        $response->assertRedirect(route('buku.index'));
    }

    public function test_can_view_buku_index_with_eager_loaded_kategori(): void
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
    }

    public function test_buku_validation_rules(): void
    {
        // Test validation: empty data
        $response = $this->post(route('buku.store'), []);
        $response->assertSessionHasErrors(['isbn', 'judul', 'penulis', 'penerbit', 'tahun_terbit', 'kategori_id', 'stok']);

        $kategori = Kategori::create([
            'kode_kategori' => 'KAT-INF',
            'nama_kategori' => 'Informatika',
        ]);

        // Test validation: judul < 5 chars, stok < 0, invalid kategori_id
        $response = $this->post(route('buku.store'), [
            'isbn' => '12345',
            'judul' => 'Abc', // < 5 chars
            'penulis' => 'John',
            'penerbit' => 'Press',
            'tahun_terbit' => 2024,
            'kategori_id' => 99999, // non-existent
            'stok' => -1, // < 0
        ]);

        $response->assertSessionHasErrors(['judul', 'kategori_id', 'stok']);
    }

    public function test_can_store_buku(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'KAT-SI',
            'nama_kategori' => 'Sistem Informasi',
        ]);

        $payload = [
            'isbn' => '978-0078022159',
            'judul' => 'Database System Concepts',
            'penulis' => 'Abraham Silberschatz',
            'penerbit' => 'McGraw-Hill',
            'tahun_terbit' => 2019,
            'kategori_id' => $kategori->id,
            'stok' => 15,
            'sinopsis' => 'Buku teks basis data terkemuka.',
        ];

        $response = $this->post(route('buku.store'), $payload);
        $response->assertRedirect(route('buku.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bukus', [
            'isbn' => '978-0078022159',
            'judul' => 'Database System Concepts',
        ]);
    }

    public function test_can_show_and_edit_buku(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'KAT-AI',
            'nama_kategori' => 'Kecerdasan Buatan',
        ]);

        $buku = Buku::create([
            'isbn' => '978-0136086208',
            'judul' => 'Artificial Intelligence: A Modern Approach',
            'penulis' => 'Stuart Russell',
            'penerbit' => 'Pearson',
            'tahun_terbit' => 2020,
            'kategori_id' => $kategori->id,
            'stok' => 8,
            'sinopsis' => 'Buku AI komprehensif.',
        ]);

        $showRes = $this->get(route('buku.show', $buku));
        $showRes->assertStatus(200);
        $showRes->assertSee('Artificial Intelligence: A Modern Approach');

        $editRes = $this->get(route('buku.edit', $buku));
        $editRes->assertStatus(200);

        $updateRes = $this->put(route('buku.update', $buku), [
            'isbn' => '978-0136086208',
            'judul' => 'Artificial Intelligence: A Modern Approach (Updated)',
            'penulis' => 'Stuart Russell & Peter Norvig',
            'penerbit' => 'Pearson',
            'tahun_terbit' => 2021,
            'kategori_id' => $kategori->id,
            'stok' => 12,
            'sinopsis' => 'Deskripsi diperbarui.',
        ]);

        $updateRes->assertRedirect(route('buku.index'));
        $this->assertDatabaseHas('bukus', [
            'id' => $buku->id,
            'judul' => 'Artificial Intelligence: A Modern Approach (Updated)',
            'stok' => 12,
        ]);
    }

    public function test_can_delete_buku(): void
    {
        $kategori = Kategori::create([
            'kode_kategori' => 'KAT-MAT',
            'nama_kategori' => 'Matematika Diskrit',
        ]);

        $buku = Buku::create([
            'isbn' => '978-0073383095',
            'judul' => 'Discrete Mathematics and Its Applications',
            'penulis' => 'Kenneth H. Rosen',
            'penerbit' => 'McGraw-Hill',
            'tahun_terbit' => 2018,
            'kategori_id' => $kategori->id,
            'stok' => 5,
        ]);

        $response = $this->delete(route('buku.destroy', $buku));
        $response->assertRedirect(route('buku.index'));
        $this->assertDatabaseMissing('bukus', ['id' => $buku->id]);
    }

    public function test_kategori_crud_and_cascade_delete(): void
    {
        $postRes = $this->post(route('kategori.store'), [
            'kode_kategori' => 'KAT-CYB',
            'nama_kategori' => 'Keamanan Siber',
        ]);
        $postRes->assertRedirect(route('kategori.index'));
        $postRes->assertSessionHas('success');

        $kategori = Kategori::where('kode_kategori', 'KAT-CYB')->firstOrFail();

        $buku = Buku::create([
            'isbn' => '978-0133594140',
            'judul' => 'Computer Networking: A Top-Down Approach',
            'penulis' => 'James Kurose',
            'penerbit' => 'Pearson',
            'tahun_terbit' => 2021,
            'kategori_id' => $kategori->id,
            'stok' => 10,
        ]);

        $showRes = $this->get(route('kategori.show', $kategori));
        $showRes->assertStatus(200);
        $showRes->assertSee('Keamanan Siber');
        $showRes->assertSee('Computer Networking: A Top-Down Approach');

        // Delete kategori and verify cascade delete on associated book
        $delRes = $this->delete(route('kategori.destroy', $kategori));
        $delRes->assertRedirect(route('kategori.index'));
        $this->assertDatabaseMissing('kategoris', ['id' => $kategori->id]);
        $this->assertDatabaseMissing('bukus', ['id' => $buku->id]);
    }
}
