/**
 * OHBONO Wallet — Journal checkout UI adapter.
 *
 * This script only displays wallet status. It deliberately does not submit
 * or authorize a payment. The Journal checkout callback must remain the
 * source of truth for final order/payment submission.
 */
(function (window, document) {
    'use strict';

    function request(url, callback) {
        fetch(url, {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            callback(null, data);
        })
        .catch(function (error) {
            callback(error);
        });
    }

    function refresh(config) {
        if (!config || !config.statusUrl) {
            return;
        }

        request(config.statusUrl, function (error, data) {
            if (error || !data || !data.success) {
                return;
            }

            var balance =
                document.querySelector(
                    '[data-ohbono-wallet-balance]'
                );

            if (balance) {
                balance.textContent =
                    data.formatted_balance;
            }

            document.dispatchEvent(
                new CustomEvent(
                    'ohbono:wallet:updated',
                    { detail: data }
                )
            );
        });
    }

    window.OhbonoWalletJournal = {
        refresh: refresh
    };
})(window, document);
