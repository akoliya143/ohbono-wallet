<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletEnvironment extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_diagnostics'
        )) {
            $this->response->setOutput(
                'Permission denied.'
            );
            return;
        }

        $this->load->model(
            'extension/ohbono/module/wallet_environment'
        );

        $tables =
            $this->model_extension_ohbono_module_wallet_environment
                ->getTables();

        $this->response->addHeader(
            'Content-Type: application/json'
        );

        $this->response->setOutput(
            json_encode([
                'opencart_version' =>
                    $this->model_extension_ohbono_module_wallet_environment
                        ->getVersion(),
                'event_table_exists' =>
                    $this->model_extension_ohbono_module_wallet_environment
                        ->getEventTableExists(),
                'wallet_tables' => $tables
            ])
        );
    }
}
