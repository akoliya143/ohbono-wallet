<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

/**
 * Journal-compatible presentation endpoint.
 *
 * This endpoint returns wallet data for the checkout UI. It never deducts
 * funds and never treats browser values as authoritative.
 */
class WalletJournal extends \Opencart\System\Engine\Controller
{
    public function status(): void
    {
        if (!$this->customer->isLogged()) {
            $this->json([
                'success' => false,
                'logged' => false
            ]);
            return;
        }

        $this->load->model(
            'extension/ohbono/module/wallet_checkout'
        );

        $balance =
            $this->model_extension_ohbono_module_wallet_checkout
                ->getAvailableBalance(
                    (int)$this->customer->getId()
                );

        $currency =
            $this->session->data['currency'];

        $this->json([
            'success' => true,
            'logged' => true,
            'balance' => $balance,
            'formatted_balance' =>
                $this->currency->format(
                    $balance,
                    $currency
                )
        ]);
    }

    private function json(array $data): void
    {
        $this->response->addHeader(
            'Content-Type: application/json'
        );

        $this->response->setOutput(
            json_encode($data)
        );
    }
}
