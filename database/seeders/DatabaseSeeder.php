<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Test Super Admin',
            'email' => 'test@example.com',
            'role' => 'super_admin',
        ]);

        // Crear registros de status
        \App\Models\Status::create(['status' => 'Pendiente']);
        \App\Models\Status::create(['status' => 'En proceso']);
        \App\Models\Status::create(['status' => 'Bloqueado']);
        \App\Models\Status::create(['status' => 'Completado']);
    }
}
