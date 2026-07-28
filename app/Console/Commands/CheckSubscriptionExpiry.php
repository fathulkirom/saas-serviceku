<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\SystemLog;
use Illuminate\Console\Command;

class CheckSubscriptionExpiry extends Command
{
    protected $signature = 'subscription:check';
    protected $description = 'Cek & auto-expire subscription/trial yang sudah habis';

    public function handle()
    {
        $this->info('Memeriksa subscription tenant...');
        $expired = 0;

        // 1. Expire trial yang sudah habis
        $trialExpired = Tenant::where('subscription_status', 'trial')
            ->where('trial_ends_at', '<=', now())
            ->get();

        foreach ($trialExpired as $tenant) {
            $tenant->update([
                'subscription_status' => 'expired',
                'is_active' => false,
            ]);
            SystemLog::warning("Trial expired: {$tenant->tenant_name} (trial ended {$tenant->trial_ends_at})");
            $expired++;
        }

        // 2. Expire subscription berbayar yang sudah habis
        $subExpired = Tenant::where('subscription_status', 'active')
            ->where('subscription_ends_at', '<=', now())
            ->get();

        foreach ($subExpired as $tenant) {
            $tenant->update([
                'subscription_status' => 'expired',
                'is_active' => false,
            ]);
            SystemLog::warning("Subscription expired: {$tenant->tenant_name} (ended {$tenant->subscription_ends_at})");
            $expired++;
        }

        $this->info("✅ {$expired} tenant di-nonaktifkan karena masa berlaku habis.");
        return Command::SUCCESS;
    }
}
