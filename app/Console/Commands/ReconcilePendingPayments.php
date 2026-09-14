<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Payment;
use App\Payments\Enums\PaymentStatus;
use App\Payments\Services\PaymentManager;
use Illuminate\Console\Command;

final class ReconcilePendingPayments extends Command
{
    protected $signature = 'payments:reconcile-pending {--limit=100 : Maximum payments to inspect}';
    protected $description = 'Verify expired pending payments and cancel them remotely if still pending.';
    public function handle(PaymentManager $payments): int
    {
        $count = 0;
        Payment::query()->where('status', PaymentStatus::Pending->value)->whereNotNull('expires_at')->where('expires_at', '<=', now())->orderBy('id')->limit((int) $this->option('limit'))->get()->each(function (Payment $payment) use ($payments, &$count): void {
            if ($payments->expire($payment)->status !== PaymentStatus::Pending) $count++;
        });
        $this->info("Reconciled {$count} expired payment(s).");
        return self::SUCCESS;
    }
}
