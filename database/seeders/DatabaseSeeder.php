<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Llamar a los seeders en el orden correcto
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            // Puedes agregar más seeders aquí (ej. para servicios, horarios, etc.)
        ]);
    }
}
