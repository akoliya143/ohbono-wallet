<?php
/**
 * OHBONO Wallet Reference Generator
 *
 * Generates trusted server-side references. Client input must never be used
 * as the sole idempotency key for a financial mutation.
 */
class OhbonoWalletReferenceService
{
    public function payment(
        int $order_id,
        string $suffix = ''
    ): string {
        if ($order_id <= 0) {
            throw new \InvalidArgumentException(
                'A valid order ID is required.'
            );
        }

        $suffix = trim($suffix);

        if ($suffix !== '' &&
            !preg_match(
                '/^[A-Za-z0-9._:-]{1,40}$/',
                $suffix
            )) {
            throw new \InvalidArgumentException(
                'Invalid reference suffix.'
            );
        }

        $reference =
            'OBW-PAY-' . $order_id;

        if ($suffix !== '') {
            $reference .= '-' . $suffix;
        }

        return $reference;
    }

    public function reversal(
        int $original_transaction_id
    ): string {
        if ($original_transaction_id <= 0) {
            throw new \InvalidArgumentException(
                'A valid original transaction ID is required.'
            );
        }

        return 'OBW-REV-' .
            $original_transaction_id;
    }
}
