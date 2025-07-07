<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Barberia;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener los roles de la base de datos
        $roleAdmin = Role::where('nombre', 'admin')->first();
        $roleDueño = Role::where('nombre', 'dueño')->first();
        $roleBarbero = Role::where('nombre', 'barbero')->first();
        $roleCliente = Role::where('nombre', 'cliente')->first();

        // 1. Crear Super Administrador
        User::create([
            'nombre' => 'Super Admin',
            'email' => 'admin@barberia.com',
            'password' => Hash::make('password'),
            'telefono' => '0999999999',
            'role_id' => $roleAdmin->id,
        ]);

        // 2. Crear Dueño de Barbería y su Barbería
        $dueño = User::create([
            'nombre' => 'Juan Dueño',
            'email' => 'dueno@barberia.com',
            'password' => Hash::make('password'),
            'telefono' => '0988888888',
            'role_id' => $roleDueño->id,
        ]);

        $barberia = Barberia::create([
            'nombre' => 'Barbería "El Buen Corte"',
            'descripcion' => 'La mejor barbería de la ciudad.',
            'direccion' => 'Avenida Siempre Viva 123',
            'telefono' => '052555555',
            'email' => 'contacto@buencorte.com',
            'estado' => 'aprobada', // La creamos aprobada para las pruebas
            'owner_id' => $dueño->id,
        ]);

        // 3. Crear Barbero (asociado al dueño y la barbería creados)
        $barberoUser = User::create([
            'nombre' => 'Pedro Barbero',
            'email' => 'barbero@barberia.com',
            'password' => Hash::make('password'),
            'telefono' => '0977777777',
            'role_id' => $roleBarbero->id,
        ]);

        // Crear el registro en la tabla 'barberos'
        $barberoUser->barbero()->create([
            'barberia_id' => $barberia->id,
            'biografia' => 'Especialista en cortes clásicos y modernos.',
            'estado' => 'activo',
        ]);

        // 4. Crear Cliente
        User::create([
            'nombre' => 'Carlos Cliente',
            'email' => 'cliente@barberia.com',
            'password' => Hash::make('password'),
            'telefono' => '0966666666',
            'role_id' => $roleCliente->id,
        ]);
    }
}
