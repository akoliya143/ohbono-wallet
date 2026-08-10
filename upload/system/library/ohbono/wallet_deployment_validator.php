<?php
/**
 * OHBONO Wallet Deployment Validator
 *
 * Validates extension-side deployment requirements without changing the
 * OpenCart installation.
 */
class OhbonoWalletDeploymentValidator
{
    public function validate(
        string $extension_root
    ): array {
        $required = [
            'extension.json',
            'upload/system/config/ohbono_wallet.php',
            'upload/system/library/ohbono/wallet_payment_service.php',
            'upload/system/library/ohbono/wallet_atomic_capture.php',
            'upload/system/library/ohbono/wallet_callback_guard.php',
            'upload/system/library/ohbono/wallet_refund_guard.php',
            'upload/system/library/ohbono/wallet_customer_order_guard.php',
            'upload/catalog/controller/extension/ohbono/module/wallet_checkout_confirm.php'
        ];

        $missing = [];

        foreach ($required as $relative) {
            if (!is_file(
                rtrim($extension_root, '/\\') .
                DIRECTORY_SEPARATOR .
                $relative
            )) {
                $missing[] = $relative;
            }
        }

        return [
            'valid' => !$missing,
            'missing' => $missing
        ];
    }
}
