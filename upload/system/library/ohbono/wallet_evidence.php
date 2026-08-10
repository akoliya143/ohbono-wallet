<?php
/**
 * OHBONO Wallet Staging Evidence
 *
 * Records test evidence separately from financial ledger data.
 * This does not mark tests as passed automatically.
 */
class OhbonoWalletEvidence
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function record(
        string $scenario,
        string $result,
        string $reference = '',
        int $order_id = 0,
        string $notes = ''
    ): int {
        $allowed = [
            'pass',
            'fail',
            'blocked',
            'not_run'
        ];

        $scenario = trim($scenario);
        $result = trim($result);
        $reference = trim($reference);
        $notes = trim($notes);

        if ($scenario === '' ||
            !in_array($result, $allowed, true)) {
            throw new \InvalidArgumentException(
                'Invalid staging evidence.'
            );
        }

        if (strlen($scenario) > 120 ||
            strlen($reference) > 160 ||
            strlen($notes) > 2000) {
            throw new \InvalidArgumentException(
                'Evidence field exceeds the allowed length.'
            );
        }

        $this->db->query(
            "INSERT INTO `" .
            DB_PREFIX . "wallet_staging_evidence`
             SET scenario = '" .
                $this->db->escape($scenario) . "',
                 result = '" .
                $this->db->escape($result) . "',
                 reference = '" .
                $this->db->escape($reference) . "',
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
            "SELECT evidence_id,
                    scenario,
                    result,
                    reference,
                    order_id,
                    notes,
                    date_added
             FROM `" .
                DB_PREFIX . "wallet_staging_evidence`
             ORDER BY evidence_id DESC
             LIMIT 500"
        )->rows;
    }
}
