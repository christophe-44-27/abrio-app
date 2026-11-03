<?php

namespace App\Console\Commands;

use App\Models\Ban;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('ban:remove {target : Email or IP address}')]
#[Description('Lift the ban on an email or an IP address')]
class UnbanCommand extends Command
{
    public function handle(): int
    {
        $target = strtolower((string) $this->argument('target'));
        $column = filter_var($target, FILTER_VALIDATE_IP) ? 'ip_address' : 'email';

        $count = Ban::where($column, $target)->delete();

        $count ? $this->info("Unbanned {$target}.") : $this->warn("No ban found for {$target}.");

        return self::SUCCESS;
    }
}
