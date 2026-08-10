<?php
namespace Opencart\Catalog\Controller\Extension\Ohbono\Module;

class WalletPreferences extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language(
            'extension/ohbono/module/wallet_preferences'
        );

        if (!$this->customer->isLogged()) {
            $this->response->redirect(
                $this->url->link('account/login')
            );
            return;
        }

        if (!$this->config->get('ohbono_wallet_status')) {
            $this->response->redirect(
                $this->url->link('account/account')
            );
            return;
        }

        $service = new \OhbonoWalletPreferenceService($this->db);

        $data['heading_title'] =
            $this->language->get('heading_title');

        $data['text_email_notifications'] =
            $this->language->get('text_email_notifications');
        $data['text_email_enabled'] =
            $this->language->get('text_email_enabled');
        $data['text_email_credit'] =
            $this->language->get('text_email_credit');
        $data['text_email_debit'] =
            $this->language->get('text_email_debit');
        $data['text_email_refund'] =
            $this->language->get('text_email_refund');
        $data['button_save'] =
            $this->language->get('button_save');
        $data['text_saved'] =
            $this->language->get('text_saved');

        $data['preferences'] =
            $service->get((int)$this->customer->getId());

        $data['action'] = $this->url->link(
            'extension/ohbono/module/wallet_preferences.save'
        );

        $data['wallet'] = $this->url->link(
            'extension/ohbono/module/wallet'
        );

        $data['header'] =
            $this->load->controller('common/header');
        $data['footer'] =
            $this->load->controller('common/footer');
        $data['column_left'] =
            $this->load->controller('common/column_left');
        $data['column_right'] =
            $this->load->controller('common/column_right');
        $data['content_top'] =
            $this->load->controller('common/content_top');
        $data['content_bottom'] =
            $this->load->controller('common/content_bottom');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_preferences',
                $data
            )
        );
    }

    public function save(): void
    {
        if (!$this->customer->isLogged()) {
            $this->json(['success' => false]);
            return;
        }

        $service = new \OhbonoWalletPreferenceService($this->db);

        $service->save(
            (int)$this->customer->getId(),
            [
                'email_enabled' =>
                    !empty($this->request->post['email_enabled']),
                'email_credit' =>
                    !empty($this->request->post['email_credit']),
                'email_debit' =>
                    !empty($this->request->post['email_debit']),
                'email_refund' =>
                    !empty($this->request->post['email_refund'])
            ]
        );

        $this->json([
            'success' => true
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
