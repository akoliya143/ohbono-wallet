<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletEvidence extends \Opencart\System\Engine\Model
{
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
             FROM `" . DB_PREFIX . "wallet_staging_evidence`
             ORDER BY evidence_id DESC
             LIMIT 500"
        )->rows;
    }
}
