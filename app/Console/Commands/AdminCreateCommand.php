<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AdminCreateCommand extends Command
{
    protected $signature = 'admin:create
                            {--name= : Yönetici adı}
                            {--email= : E-posta adresi}
                            {--password= : Parola (en az 12 karakter)}';

    protected $description = 'İlk süper yönetici veya yeni yönetici kullanıcısı oluşturur';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Ad Soyad');
        $email = $this->option('email') ?: $this->ask('E-posta');
        $password = $this->option('password') ?: $this->secret('Parola (en az 12 karakter)');
        $passwordConfirmation = $this->option('password') ? null : $this->secret('Parola tekrar');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation ?? $password,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $role = User::query()->where('role', UserRole::SuperAdmin)->exists()
            ? UserRole::Editor
            : UserRole::SuperAdmin;

        $user = User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
            'is_active' => true,
        ]);

        $this->info("Kullanıcı oluşturuldu: {$user->email} ({$user->role->label()})");

        return self::SUCCESS;
    }
}
