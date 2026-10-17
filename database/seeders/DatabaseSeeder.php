<?php

namespace Database\Seeders;

use App\Models\Buku;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Pustakawan Uji (Sesuai Spesifikasi Wajib Modul Minggu 6)
        User::create([
            'name' => 'Pustakawan Del',
            'email' => 'pustakawan@del.ac.id',
            'password' => Hash::make('password123'),
        ]);

        $kategoris = [
            [
                'kode_kategori' => 'KAT-INF',
                'nama_kategori' => 'Informatika & Algoritma Pemrograman',
            ],
            [
                'kode_kategori' => 'KAT-SI',
                'nama_kategori' => 'Sistem Informasi & Manajemen Basis Data',
            ],
            [
                'kode_kategori' => 'KAT-RPL',
                'nama_kategori' => 'Rekayasa Perangkat Lunak & Arsitektur',
            ],
            [
                'kode_kategori' => 'KAT-AI',
                'nama_kategori' => 'Kecerdasan Buatan & Pembelajaran Mesin',
            ],
            [
                'kode_kategori' => 'KAT-CYB',
                'nama_kategori' => 'Keamanan Siber & Jaringan Komputer',
            ],
            [
                'kode_kategori' => 'KAT-MAT',
                'nama_kategori' => 'Matematika Diskrit & Sains Terapan',
            ],
        ];

        $kategoriModels = [];
        foreach ($kategoris as $data) {
            $kategoriModels[] = Kategori::create($data);
        }

        $sampleBukus = [
            [
                'isbn' => '978-0132350884',
                'judul' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'penulis' => 'Robert C. Martin',
                'penerbit' => 'Prentice Hall',
                'tahun_terbit' => 2008,
                'kategori_id' => $kategoriModels[2]->id, // RPL
                'stok' => 12,
                'sinopsis' => 'Panduan komprehensif tentang penulisan kode yang bersih, mudah dibaca, dan mudah dipelihara untuk pengembang profesional.',
            ],
            [
                'isbn' => '978-0201633610',
                'judul' => 'Design Patterns: Elements of Reusable Object-Oriented Software',
                'penulis' => 'Erich Gamma, Richard Helm, Ralph Johnson, John Vlissides',
                'penerbit' => 'Addison-Wesley Professional',
                'tahun_terbit' => 1994,
                'kategori_id' => $kategoriModels[2]->id, // RPL
                'stok' => 8,
                'sinopsis' => 'Koleksi pola desain berorientasi objek klasik yang menjadi fondasi arsitektur perangkat lunak modern.',
            ],
            [
                'isbn' => '978-0134757599',
                'judul' => 'Refactoring: Improving the Design of Existing Code',
                'penulis' => 'Martin Fowler',
                'penerbit' => 'Addison-Wesley',
                'tahun_terbit' => 2018,
                'kategori_id' => $kategoriModels[2]->id, // RPL
                'stok' => 10,
                'sinopsis' => 'Buku pegangan wajib tentang bagaimana merestrukturisasi kode warisan tanpa mengubah perilaku eksternalnya.',
            ],
            [
                'isbn' => '978-0136086208',
                'judul' => 'Artificial Intelligence: A Modern Approach (4th Edition)',
                'penulis' => 'Stuart Russell, Peter Norvig',
                'penerbit' => 'Pearson',
                'tahun_terbit' => 2020,
                'kategori_id' => $kategoriModels[3]->id, // AI
                'stok' => 15,
                'sinopsis' => 'Buku teks standar dunia dalam teori, implementasi, dan etika kecerdasan buatan dan machine learning modern.',
            ],
            [
                'isbn' => '978-1492032649',
                'judul' => 'Hands-On Machine Learning with Scikit-Learn, Keras, and TensorFlow',
                'penulis' => 'Aurélien Géron',
                'penerbit' => "O'Reilly Media",
                'tahun_terbit' => 2022,
                'kategori_id' => $kategoriModels[3]->id, // AI
                'stok' => 14,
                'sinopsis' => 'Panduan praktis membangun model kecerdasan buatan dari konsep dasar hingga deep neural network tingkat lanjut.',
            ],
            [
                'isbn' => '978-0078022159',
                'judul' => 'Database System Concepts (7th Edition)',
                'penulis' => 'Abraham Silberschatz, Henry F. Korth, S. Sudarshan',
                'penerbit' => 'McGraw-Hill Education',
                'tahun_terbit' => 2019,
                'kategori_id' => $kategoriModels[1]->id, // SI
                'stok' => 20,
                'sinopsis' => 'Prinsip fundamental sistem manajemen basis data, normalisasi, pemrosesan transaksi, dan arsitektur data terdistribusi.',
            ],
            [
                'isbn' => '978-1491950357',
                'judul' => 'Designing Data-Intensive Applications',
                'penulis' => 'Martin Kleppmann',
                'penerbit' => "O'Reilly Media",
                'tahun_terbit' => 2017,
                'kategori_id' => $kategoriModels[1]->id, // SI
                'stok' => 18,
                'sinopsis' => 'Panduan mendalam arsitektur sistem data modern, keandalan, skalabilitas, dan konsistensi data.',
            ],
            [
                'isbn' => '978-0262033848',
                'judul' => 'Introduction to Algorithms (CLRS 3rd Edition)',
                'penulis' => 'Thomas H. Cormen, Charles E. Leiserson, Ronald L. Rivest, Clifford Stein',
                'penerbit' => 'MIT Press',
                'tahun_terbit' => 2009,
                'kategori_id' => $kategoriModels[0]->id, // INF
                'stok' => 25,
                'sinopsis' => 'Buku rujukan utama tentang analisis kompleksitas algoritma, struktur data lanjut, dan teori komputasi.',
            ],
            [
                'isbn' => '978-0131103627',
                'judul' => 'The C Programming Language (2nd Edition)',
                'penulis' => 'Brian W. Kernighan, Dennis M. Ritchie',
                'penerbit' => 'Prentice Hall',
                'tahun_terbit' => 1988,
                'kategori_id' => $kategoriModels[0]->id, // INF
                'stok' => 7,
                'sinopsis' => 'Karya otoritatif pencipta bahasa C yang membahas konsep memori, pointer, dan sistem pemrograman secara mendalam.',
            ],
            [
                'isbn' => '978-0133594140',
                'judul' => 'Computer Networking: A Top-Down Approach',
                'penulis' => 'James Kurose, Keith Ross',
                'penerbit' => 'Pearson',
                'tahun_terbit' => 2021,
                'kategori_id' => $kategoriModels[4]->id, // CYB
                'stok' => 16,
                'sinopsis' => 'Pemahaman komprehensif tentang protokol jaringan, lapisan aplikasi hingga fisik, serta keamanan komunikasi data.',
            ],
            [
                'isbn' => '978-1118915615',
                'judul' => 'The Web Application Hacker’s Handbook: Finding and Exploiting Security Flaws',
                'penulis' => 'Dafydd Stuttard, Marcus Pinto',
                'penerbit' => 'Wiley',
                'tahun_terbit' => 2011,
                'kategori_id' => $kategoriModels[4]->id, // CYB
                'stok' => 6,
                'sinopsis' => 'Panduan praktis pengujian penetrasi aplikasi web, pencegahan injeksi SQL, XSS, CSRF, dan kerentanan keamanan kritis.',
            ],
            [
                'isbn' => '978-0073383095',
                'judul' => 'Discrete Mathematics and Its Applications (8th Edition)',
                'penulis' => 'Kenneth H. Rosen',
                'penerbit' => 'McGraw-Hill Education',
                'tahun_terbit' => 2018,
                'kategori_id' => $kategoriModels[5]->id, // MAT
                'stok' => 22,
                'sinopsis' => 'Fondasi logika proposisional, teori himpunan, kombinatorika, relasi graf, dan induksi matematika untuk ilmu komputer.',
            ],
            // 2. Sampel Buku dengan STOK = 0 (Wajib Modul Minggu 6: Uji Tolak & Rollback Stok Habis)
            [
                'isbn' => '978-0131429383',
                'judul' => 'Modern Operating Systems (Stok Habis - Uji ACID Rollback)',
                'penulis' => 'Andrew S. Tanenbaum, Herbert Bos',
                'penerbit' => 'Pearson Education',
                'tahun_terbit' => 2015,
                'kategori_id' => $kategoriModels[0]->id, // INF
                'stok' => 0,
                'sinopsis' => 'Buku sampel khusus pengujian penolakan transaksi peminjaman secara atomik ketika stok bernilai 0.',
            ],
            // 3. Sampel Buku dengan STOK = 1 (Wajib Modul Minggu 6: Uji Concurrency Locking lockForUpdate)
            [
                'isbn' => '978-0134444321',
                'judul' => 'Cryptography and Network Security (Stok 1 - Uji LockForUpdate)',
                'penulis' => 'William Stallings',
                'penerbit' => 'Pearson',
                'tahun_terbit' => 2017,
                'kategori_id' => $kategoriModels[4]->id, // CYB
                'stok' => 1,
                'sinopsis' => 'Buku sampel khusus pengujian atomisitas transaksi peminjaman pada sisa stok tepat 1 eksamplar.',
            ],
        ];

        $createdBukus = [];
        foreach ($sampleBukus as $bukuData) {
            $createdBukus[] = Buku::create($bukuData);
        }

        // Generate additional 15 realistic books using Factory to guarantee > 25 books across categories
        foreach ($kategoriModels as $kategori) {
            Buku::factory(3)->create([
                'kategori_id' => $kategori->id,
            ]);
        }

        // 4. Sampel Transaksi Peminjaman (Wajib Modul Minggu 6)
        // a. Peminjaman Aktif (status: 'dipinjam')
        Peminjaman::create([
            'kode_pinjam' => 'PINJAM-20261010-0001',
            'buku_id' => $createdBukus[0]->id, // Clean Code
            'nim_peminjam' => '12S24001',
            'nama_peminjam' => 'Budi Santoso',
            'tanggal_pinjam' => now()->subDays(3)->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(4)->toDateString(),
            'tanggal_kembali' => null,
            'status' => 'dipinjam',
        ]);

        Peminjaman::create([
            'kode_pinjam' => 'PINJAM-20261012-0002',
            'buku_id' => $createdBukus[3]->id, // AI A Modern Approach
            'nim_peminjam' => '12S24005',
            'nama_peminjam' => 'Ruth Simanjuntak',
            'tanggal_pinjam' => now()->subDays(2)->toDateString(),
            'tanggal_jatuh_tempo' => now()->addDays(5)->toDateString(),
            'tanggal_kembali' => null,
            'status' => 'dipinjam',
        ]);

        // b. Peminjaman Selesai (status: 'dikembalikan')
        Peminjaman::create([
            'kode_pinjam' => 'PINJAM-20261001-0003',
            'buku_id' => $createdBukus[5]->id, // Database System Concepts
            'nim_peminjam' => '12S24010',
            'nama_peminjam' => 'Yohana Panjaitan',
            'tanggal_pinjam' => now()->subDays(12)->toDateString(),
            'tanggal_jatuh_tempo' => now()->subDays(5)->toDateString(),
            'tanggal_kembali' => now()->subDays(6)->toDateString(),
            'status' => 'dikembalikan',
        ]);

        Peminjaman::create([
            'kode_pinjam' => 'PINJAM-20261002-0004',
            'buku_id' => $createdBukus[1]->id, // Design Patterns
            'nim_peminjam' => '12S24018',
            'nama_peminjam' => 'Kelvin Marpaung',
            'tanggal_pinjam' => now()->subDays(10)->toDateString(),
            'tanggal_jatuh_tempo' => now()->subDays(3)->toDateString(),
            'tanggal_kembali' => now()->subDays(3)->toDateString(),
            'status' => 'dikembalikan',
        ]);
    }
}
