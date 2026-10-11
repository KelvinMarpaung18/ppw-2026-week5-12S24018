<?php

namespace Database\Factories;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Buku>
 */
class BukuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'isbn' => fake()->unique()->isbn13(),
            'judul' => fake()->sentence(fake()->numberBetween(3, 7)),
            'penulis' => fake()->name(),
            'penerbit' => fake()->company() . ' Press',
            'tahun_terbit' => fake()->numberBetween(2010, 2026),
            'kategori_id' => Kategori::factory(),
            'stok' => fake()->numberBetween(1, 50),
            'sinopsis' => fake()->paragraph(3),
        ];
    }
}
