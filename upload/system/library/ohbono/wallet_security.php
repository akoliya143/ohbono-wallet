<?php
/**
 * OHBONO Wallet Security Helpers
 *
 * Small, dependency-light helpers for validating wallet references and amounts.
 * These helpers are not a replacement for OpenCart authentication/permissions.
 */
class OhbonoWalletSecurity
{
    public static function normalizeAmount($amount): float
    {
        if (!is_numeric($amount)) {
            return 0.0;
        }

        return round(max(0.0, (float)$amount), 4);
    }

    public static function validateReference(
        string $reference,
        int $max_length = 100
    ): string {
        $reference = trim($reference);

        if ($reference === '') {
            throw new \InvalidArgumentException(
                'Payment reference is required.'
            );
        }

        if (strlen($reference) > $max_length) {
            throw new \InvalidArgumentException(
                'Payment reference is too long.'
            );
        }

        if (!preg_match(
            '/^[A-Za-z0-9][A-Za-z0-9._:-]*$/',
            $reference
        )) {
            throw new \InvalidArgumentException(
                'Payment reference contains invalid characters.'
            );
        }

        return $reference;
    }

    public static function validateReason(
        string $reason,
        int $min_length = 5,
        int $max_length = 500
    ): string {
        $reason = trim($reason);

        if (strlen($reason) < $min_length) {
            throw new \InvalidArgumentException(
                'A meaningful reason is required.'
            );
        }

        if (strlen($reason) > $max_length) {
            throw new \InvalidArgumentException(
                'Reason is too long.'
            );
        }

        return $reason;
    }
}
