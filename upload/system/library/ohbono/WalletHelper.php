<?php
namespace Opencart\System\Library\Ohbono;

/**
 * Small helpers shared by the wallet domain layer.
 */
class WalletHelper
{
    /**
     * Normalise a monetary value to the wallet precision.
     *
     * The wallet stores four decimal places. This method is deliberately
     * simple; database values are still treated as decimal strings by the
     * repository when possible.
     */
    public static function amount($amount): float
    {
        return round((float)$amount, 4);
    }

    public static function positiveAmount($amount): float
    {
        $amount = self::amount($amount);

        if ($amount <= 0) {
            throw new WalletException('Wallet amount must be greater than zero.');
        }

        return $amount;
    }

    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    public static function transactionType(string $type): string
    {
        return preg_replace('/[^a-z0-9_]/i', '_', strtolower($type));
    }
}
