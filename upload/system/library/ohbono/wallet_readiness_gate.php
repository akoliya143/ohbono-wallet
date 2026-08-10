<?php
/**
 * OHBONO Wallet Production Readiness Gate
 *
 * This gate is intentionally conservative. It checks structural prerequisites
 * and does not claim that a live payment provider or Journal checkout has been
 * tested.
 */
class OhbonoWalletReadinessGate
{
    public function evaluate(array $checks): array
    {
        $required = [
            'database_schema',
            'admin_permissions',
            'wallet_capture_idempotency',
            'refund_reversal',
            'journal_checkout_verified',
            'staging_wallet_only',
            'staging_partial_wallet',
            'staging_failure_reconciliation',
            'production_backup'
        ];

        $missing = [];

        foreach ($required as $key) {
            if (empty($checks[$key])) {
                $missing[] = $key;
            }
        }

        return [
            'ready' => !$missing,
            'missing' => $missing
        ];
    }
}
