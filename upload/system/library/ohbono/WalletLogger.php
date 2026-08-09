<?php
namespace Opencart\System\Library\Ohbono;

/**
 * Operational wallet logger.
 */
class WalletLogger
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function write(
        string $level,
        string $message,
        int $customer_id = 0,
        int $transaction_id = 0,
        array $context = []
    ): void {
        $allowed = ['info', 'warning', 'error'];

        if (!in_array($level, $allowed, true)) {
            $level = 'info';
        }

        $json = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($json === false) {
            $json = '{}';
        }

        $this->db->query(
            "INSERT INTO `" . DB_PREFIX . "wallet_log`
             SET `customer_id` = '" . (int)$customer_id . "',
                 `transaction_id` = '" . (int)$transaction_id . "',
                 `level` = '" . $this->db->escape($level) . "',
                 `message` = '" . $this->db->escape(mb_substr($message, 0, 2000)) . "',
                 `context` = '" . $this->db->escape($json) . "',
                 `date_added` = NOW()"
        );
    }

    public function info(string $message, int $customer_id = 0, array $context = []): void
    {
        $this->write('info', $message, $customer_id, 0, $context);
    }

    public function warning(string $message, int $customer_id = 0, array $context = []): void
    {
        $this->write('warning', $message, $customer_id, 0, $context);
    }

    public function error(string $message, int $customer_id = 0, array $context = []): void
    {
        $this->write('error', $message, $customer_id, 0, $context);
    }
}
