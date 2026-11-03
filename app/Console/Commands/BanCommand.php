<?php

namespace App\Console\Commands;

use App\Models\Ban;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ban:add {target : Email or IP address} {--email= : Also ban this email} {--ip= : Also ban this IP address} {--reason= : Why}')]
#[Description('Ban an email, an IP address, or both')]
class BanCommand extends Command
{
    public function handle(): int
    {
        $target = (string) $this->argument('target');
        $email = $this->option('email');
        $ip = $this->option('ip');

        filter_var($target, FILTER_VALIDATE_IP) ? $ip ??= $target : $email ??= $target;

        if ($email && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("Invalid email: {$email}");

            return self::FAILURE;
        }

        if ($ip && ! filter_var($ip, FILTER_VALIDATE_IP)) {
            $this->error("Invalid IP address: {$ip}");

            return self::FAILURE;
        }

        Ban::create(['email' => $email, 'ip_address' => $ip, 'reason' => $this->option('reason')]);

        $this->info('Banned '.collect([$email, $ip])->filter()->implode(' + ').'.');

        return self::SUCCESS;
    }
}
