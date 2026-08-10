<?php
/**
 * OHBONO Wallet Currency Helper
 *
 * Wallet amounts are stored as account values. Display formatting should use
 * the active OpenCart currency context instead of hard-coded symbols.
 */
class OhbonoWalletCurrencyService
{
    private $currency;

    public function __construct($currency)
    {
        $this->currency = $currency;
    }

    public function format(
        float $amount,
        string $currency_code = ''
    ): string {
        $currency_code = $currency_code ?: '';

        return $this->currency->format(
            $amount,
            $currency_code
        );
    }
}
