<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

/**
 * There is no registration in the admin; administrators are created here.
 */
class CreateAdmin extends Command
{
    protected $signature = 'app:create-admin
        {--name= : Name of the administrator}
        {--email= : E-mail used to log in}
        {--password= : Password (prompted when omitted; avoid on shared machines)}';

    protected $description = 'Create an administrator for the admin panel';

    public function handle(): int
    {
        $data = [
            'name' => $this->option('name') ?: text('Meno', required: true),
            'email' => $this->option('email') ?: text('E-mail', required: true),
            'password' => $this->option('password') ?: password('Heslo (min. 12 znakov)', required: true),
        ];

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', Password::min(12)],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::query()->create($validator->validated());

        $this->info("Administrátor {$data['email']} bol vytvorený. Pri prvom prihlásení si nastaví overovaciu aplikáciu (MFA).");

        return self::SUCCESS;
    }
}
