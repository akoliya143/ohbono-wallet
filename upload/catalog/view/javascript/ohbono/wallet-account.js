/**
 * OHBONO Wallet account-menu helper.
 *
 * The script is deliberately non-invasive. It does not replace Journal
 * account markup. Stores can add a marker:
 *
 *   data-ohbono-wallet-menu
 *
 * to the account navigation container and the wallet item will be appended.
 */
(function (window, document) {
    'use strict';

    var initialized = false;

    function getMenuContainer() {
        return document.querySelector('[data-ohbono-wallet-menu]');
    }

    function getWalletUrl() {
        var element = document.querySelector('[data-ohbono-wallet-url]');

        return element ? element.getAttribute('data-ohbono-wallet-url') : '';
    }

    function addWalletItem() {
        var menu = getMenuContainer();
        var url = getWalletUrl();

        if (!menu || !url) {
            return false;
        }

        if (menu.querySelector('[data-ohbono-wallet-link]')) {
            return true;
        }

        var item = document.createElement('a');

        item.href = url;
        item.setAttribute('data-ohbono-wallet-link', '1');
        item.className = 'list-group-item list-group-item-action';

        item.innerHTML =
            '<i class="fa-solid fa-wallet fa-fw me-2"></i>' +
            '<span>My Wallet</span>';

        menu.appendChild(item);

        return true;
    }

    function init() {
        if (initialized) {
            return;
        }

        initialized = true;
        addWalletItem();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.ohbonoWalletAccount = {
        init: init,
        add: addWalletItem
    };
}(window, document));
