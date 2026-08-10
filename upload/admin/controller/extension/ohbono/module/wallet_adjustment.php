<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletAdjustment extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language(
            'extension/ohbono/module/wallet_adjustment'
        );

        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_adjustment'
        )) {
            $this->response->setOutput(
                $this->language->get('error_permission')
            );
            return;
        }

        $customer_id = (int)(
            $this->request->get['customer_id'] ?? 0
        );

        $this->load->model(
            'extension/ohbono/module/wallet_customer'
        );

        $data['customer'] =
            $this->model_extension_ohbono_module_wallet_customer
                ->getCustomerWallet($customer_id);

        $data['heading_title'] =
            $this->language->get('heading_title');

        $data['text_credit'] =
            $this->language->get('text_credit');
        $data['text_debit'] =
            $this->language->get('text_debit');
        $data['text_reason'] =
            $this->language->get('text_reason');
        $data['text_reference'] =
            $this->language->get('text_reference');
        $data['text_amount'] =
            $this->language->get('text_amount');
        $data['button_submit'] =
            $this->language->get('button_submit');

        $data['action'] = $this->url->link(
            'extension/ohbono/module/wallet_adjustment.adjust'
        );

        $data['customer_id'] = $customer_id;

        $data['header'] =
            $this->load->controller('common/header');
        $data['column_left'] =
            $this->load->controller('common/column_left');
        $data['footer'] =
            $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_adjustment',
                $data
            )
        );
    }

    public function adjust(): void
    {
        $this->load->language(
            'extension/ohbono/module/wallet_adjustment'
        );

        if (!$this->user->hasPermission(
            'modify',
            'extension/ohbono/module/wallet_adjustment'
        )) {
            $this->json([
                'success' => false,
                'error' => $this->language->get(
                    'error_permission'
                )
            ]);
            return;
        }

        $customer_id = (int)(
            $this->request->post['customer_id'] ?? 0
        );

        $amount = (float)(
            $this->request->post['amount'] ?? 0
        );

        $direction = (string)(
            $this->request->post['direction'] ?? ''
        );

        $reason = trim((string)(
            $this->request->post['reason'] ?? ''
        ));

        $reference = trim((string)(
            $this->request->post['reference'] ?? ''
        ));

        if ($customer_id <= 0 ||
            $amount <= 0 ||
            $reason === '' ||
            $reference === '') {
            $this->json([
                'success' => false,
                'error' => $this->language->get(
                    'error_validation'
                )
            ]);
            return;
        }

        try {
            $service =
                new \OhbonoWalletAdminAdjustmentService(
                    $this->db
                );

            $transaction_id =
                $service->adjust(
                    (int)$this->user->getId(),
                    $customer_id,
                    $amount,
                    $direction,
                    $reason,
                    $reference
                );

            $this->json([
                'success' => true,
                'transaction_id' => $transaction_id
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
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
