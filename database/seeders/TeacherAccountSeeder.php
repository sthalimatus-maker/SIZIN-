<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherAccountSeeder extends Seeder
{
    public function run(): void
    {
        $username = env('TEACHER_USERNAME');
        $email = env('TEACHER_EMAIL');
        $password = env('TEACHER_PASSWORD');

        if (! $username || ! $email || ! $password) {
            $this->command?->warn('Akun guru dilewati. Isi TEACHER_USERNAME, TEACHER_EMAIL, dan TEACHER_PASSWORD pada .env lalu jalankan db:seed.');
            return;
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            ['name' => env('TEACHER_NAME', 'Guru Piket'), 'username' => $username, 'role' => 'guru', 'password' => Hash::make($password)],
        );
        $this->command?->info('Akun guru berhasil disiapkan.');
    }
}
