/**
 * OHBONO Wallet — Journal checkout bridge.
 *
 * This bridge only collects the customer's selected wallet amount and exposes
 * it to the site's confirmed checkout callback. It does not trust the amount
 * for final accounting and does not directly mutate the wallet.
 */
(function (window, document) {
    'use strict';

    function getAmount() {
        var input = document.querySelector(
            '[data-ohbono-wallet-amount]'
        );

        if (!input) {
            return 0;
        }

        var amount = parseFloat(input.value || '0');

        return isFinite(amount) && amount > 0
            ? amount
            : 0;
    }

    function getOrderId() {
        var input = document.querySelector(
            '[data-ohbono-order-id]'
        );

        if (!input) {
            return 0;
        }

        var orderId = parseInt(
            input.value || '0',
            10
        );

        return isFinite(orderId) && orderId > 0
            ? orderId
            : 0;
    }

    function getPayload() {
        return {
            order_id: getOrderId(),
            amount: getAmount()
        };
    }

    function attach(config) {
        if (!config) {
            return;
        }

        window.OhbonoWalletJournalCheckout = {
            getPayload: getPayload,
            endpoint: config.endpoint || ''
        };

        document.dispatchEvent(
            new CustomEvent(
                'ohbono:wallet:checkout-ready'
            )
        );
    }

    window.OhbonoWalletJournalCheckoutBridge = {
        attach: attach,
        getPayload: getPayload
    };
})(window, document);
