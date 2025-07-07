<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['nombre' => 'admin']);
        Role::create(['nombre' => 'dueño']);
        Role::create(['nombre' => 'barbero']);
        Role::create(['nombre' => 'cliente']);
    }
}
