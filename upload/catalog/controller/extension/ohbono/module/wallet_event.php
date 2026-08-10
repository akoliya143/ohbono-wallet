<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

class WalletEvent extends \Opencart\System\Engine\Controller
{
    public function checkoutBefore(
        string &$route = '',
        array &$args = [],
        mixed &$output = null
    ): void {
        /*
         * Preparation only. Never deduct wallet funds in a before event.
         */
    }

    public function orderAfter(
        string &$route = '',
        array &$args = [],
        mixed &$output = null
    ): void {
        /*
         * Order creation alone is not proof that payment succeeded.
         */
    }
}
