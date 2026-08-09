/**
 * OHBONO Wallet - Journal 4 checkout adapter
 *
 * This file intentionally does not overwrite Journal core JavaScript.
 * It listens for the OHBONO wallet event and attempts to refresh the
 * OpenCart checkout using the public checkout route.
 *
 * Stores with a custom Journal checkout implementation can override
 * window.ohbonoJournalWalletRefresh() to call the exact Journal refresh
 * method used by their installation.
 */
(function (window, document) {
    'use strict';

    function postCheckoutUpdate() {
        if (typeof window.ohbonoJournalWalletRefresh === 'function') {
            return Promise.resolve(
                window.ohbonoJournalWalletRefresh()
            );
        }

        /*
         * Journal 4 installations can expose different checkout refresh
         * implementations depending on the checkout layout/configuration.
         *
         * Prefer a native OpenCart checkout refresh event if one exists.
         */
        document.dispatchEvent(new CustomEvent('ohbonoWalletRefreshCheckout'));

        /*
         * Do not blindly reload the page. Journal's checkout can contain
         * unsaved address/payment state, so an automatic full reload would
         * be unsafe.
         */
        return Promise.resolve(false);
    }

    document.addEventListener('ohbonoWalletChanged', function (event) {
        var detail = event && event.detail ? event.detail : {};

        document.dispatchEvent(new CustomEvent(
            'ohbonoWalletCheckoutUpdating',
            { detail: detail }
        ));

        postCheckoutUpdate().finally(function () {
            document.dispatchEvent(new CustomEvent(
                'ohbonoWalletCheckoutUpdated',
                { detail: detail }
            ));
        });
    });

    window.ohbonoWalletJournal4 = {
        refresh: postCheckoutUpdate
    };
}(window, document));
