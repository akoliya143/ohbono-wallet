<?php
namespace Opencart\Admin\Controller\Extension\Ohbono\Module;

class WalletEmailQueue extends \Opencart\System\Engine\Controller
{
    public function index(): void
    {
        $this->load->language(
            'extension/ohbono/module/wallet_email_queue'
        );

        if (!$this->user->hasPermission(
            'access',
            'extension/ohbono/module/wallet_email_queue'
        )) {
            $this->response->setOutput(
                $this->language->get('error_permission')
            );
            return;
        }

        $this->load->model(
            'extension/ohbono/module/wallet_email_queue'
        );

        $filter_status =
            (string)($this->request->get['filter_status'] ?? '');

        $data['heading_title'] =
            $this->language->get('heading_title');

        $data['text_no_results'] =
            $this->language->get('text_no_results');

        $data['text_all'] =
            $this->language->get('text_all');

        $data['text_filter'] =
            $this->language->get('text_filter');

        $data['queues'] =
            $this->model_extension_ohbono_module_wallet_email_queue
                ->getQueues(
                    0,
                    100,
                    $filter_status
                );

        $data['stats'] =
            $this->model_extension_ohbono_module_wallet_email_queue
                ->getStats();

        $data['filter_status'] = $filter_status;

        $data['retry_url'] = $this->url->link(
            'extension/ohbono/module/wallet_email_queue.retry'
        );

        $data['header'] =
            $this->load->controller('common/header');

        $data['column_left'] =
            $this->load->controller('common/column_left');

        $data['footer'] =
            $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view(
                'extension/ohbono/module/wallet_email_queue',
                $data
            )
        );
    }

    public function retry(): void
    {
        if (!$this->user->hasPermission(
            'modify',
            'extension/ohbono/module/wallet_email_queue'
        )) {
            $this->json(['success' => false]);
            return;
        }

        $queue_id = (int)(
            $this->request->post['queue_id'] ?? 0
        );

        $this->load->model(
            'extension/ohbono/module/wallet_email_queue'
        );

        $success =
            $this->model_extension_ohbono_module_wallet_email_queue
                ->retry($queue_id);

        $this->json([
            'success' => $success
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
