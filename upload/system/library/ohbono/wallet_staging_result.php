<?php
/**
 * OHBONO Wallet Staging Result Store
 *
 * Stores manual staging-test results. This is not a financial ledger.
 */
class OhbonoWalletStagingResult
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function record(
        string $scenario,
        string $result,
        string $notes = '',
        int $order_id = 0
    ): int {
        $scenario = trim($scenario);
        $result = trim($result);
        $notes = trim($notes);

        $allowed = [
            'pass',
            'fail',
            'blocked',
            'not_run'
        ];

        if ($scenario === '' ||
            !in_array($result, $allowed, true)) {
            throw new \InvalidArgumentException(
                'Invalid staging result.'
            );
        }

        if (strlen($scenario) > 100 ||
            strlen($notes) > 1000) {
            throw new \InvalidArgumentException(
                'Staging result text is too long.'
            );
        }

        $this->db->query(
            "INSERT INTO `" .
            DB_PREFIX . "wallet_staging_result`
             SET scenario = '" .
                $this->db->escape($scenario) . "',
                 result = '" .
                $this->db->escape($result) . "',
                 order_id = '" .
                (int)$order_id . "',
                 notes = '" .
                $this->db->escape($notes) . "',
                 date_added = NOW()"
        );

        return (int)$this->db->getLastId();
    }

    public function getLatest(): array
    {
        return $this->db->query(
            "SELECT staging_result_id,
                    scenario,
                    result,
                    order_id,
                    notes,
                    date_added
             FROM `" .
                DB_PREFIX . "wallet_staging_result`
             ORDER BY staging_result_id DESC
             LIMIT 200"
        )->rows;
    }
}
