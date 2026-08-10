<?php
/**
 * OHBONO Wallet Mail Service.
 *
 * Email delivery is deliberately outside the financial transaction path.
 * A failed email must never roll back a completed wallet mutation.
 */
class OhbonoWalletMailService
{
    private $db;
    private $config;
    private $mail;

    public function __construct($registry)
    {
        $this->db = $registry->get('db');
        $this->config = $registry->get('config');

        $this->mail = new \Opencart\System\Library\Mail(
            $this->config->get('config_mail_engine')
        );

        $this->mail->parameter = $this->config->get('config_mail_parameter');
        $this->mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        $this->mail->smtp_username = $this->config->get('config_mail_smtp_username');
        $this->mail->smtp_password = $this->config->get('config_mail_smtp_password');
        $this->mail->smtp_port = $this->config->get('config_mail_smtp_port');
        $this->mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');
    }

    public function sendWalletNotification(
        int $customer_id,
        int $transaction_id,
        string $subject,
        string $message
    ): bool {
        if ($customer_id <= 0 || $transaction_id <= 0) {
            return false;
        }

        if (trim($subject) === '' || trim($message) === '') {
            return false;
        }

        $customer = $this->db->query(
            "SELECT email, firstname, lastname
             FROM `" . DB_PREFIX . "customer`
             WHERE customer_id = '" . (int)$customer_id . "'
             LIMIT 1"
        );

        if (!$customer->num_rows || !filter_var($customer->row['email'], FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        $this->mail->setTo($customer->row['email']);
        $this->mail->setFrom($this->config->get('config_email'));
        $this->mail->setSender($this->config->get('config_name'));
        $this->mail->setSubject($subject);
        $this->mail->setHtml(
            $this->buildHtml(
                $customer->row['firstname'],
                $message,
                $transaction_id
            )
        );

        $this->mail->send();

        return true;
    }

    private function buildHtml(
        string $firstname,
        string $message,
        int $transaction_id
    ): string {
        $name = htmlspecialchars($firstname, ENT_QUOTES, 'UTF-8');
        $body = nl2br(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));

        return '<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>OHBONO Wallet</title>
</head>
<body style="font-family:Arial,sans-serif;line-height:1.6;">
    <h2>OHBONO Wallet</h2>
    <p>Hello ' . $name . ',</p>
    <p>' . $body . '</p>
    <p style="color:#777;font-size:12px;">
        Wallet transaction #' . (int)$transaction_id . '
    </p>
</body>
</html>';
    }
}
