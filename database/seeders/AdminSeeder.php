<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario admin super seguro
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@surveys.com',
            'password' => Hash::make('Admin@2025!SecureP4ss#Survey'),
            'is_admin' => true,
            'email_verified_at' => now(),
        ]);

        // Mostrar credenciales en consola
        $this->command->info('====================================');
        $this->command->info('Admin User Created Successfully!');
        $this->command->info('====================================');
        $this->command->info('Email: admin@surveys.com');
        $this->command->info('Password: Admin@2025!SecureP4ss#Survey');
        $this->command->info('====================================');
        $this->command->warn('¡IMPORTANTE! Guarda estas credenciales de forma segura.');
    }
}
