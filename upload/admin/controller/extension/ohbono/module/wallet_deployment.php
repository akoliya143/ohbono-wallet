<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletDeployment extends \Opencart\System\Engine\Controller
{
    public function validate(): void
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

        $root = defined('DIR_APPLICATION')
            ? dirname(DIR_APPLICATION)
            : '';

        $this->response->addHeader(
            'Content-Type: application/json'
        );

        if ($root === '') {
            $this->response->setOutput(
                json_encode([
                    'valid' => false,
                    'error' => 'Application root unavailable.'
                ])
            );
            return;
        }

        $validator =
            new \OhbonoWalletDeploymentValidator();

        $this->response->setOutput(
            json_encode(
                $validator->validate($root)
            )
        );
    }
}
