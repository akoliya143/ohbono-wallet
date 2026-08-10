<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

class WalletStaging extends \Opencart\System\Engine\Model
{
    public function getLatest(): array
    {
        return $this->db->query(
            "SELECT staging_result_id,
                    scenario,
                    result,
                    order_id,
                    notes,
                    date_added
             FROM `" . DB_PREFIX . "wallet_staging_result`
             ORDER BY staging_result_id DESC
             LIMIT 200"
        )->rows;
    }
}
