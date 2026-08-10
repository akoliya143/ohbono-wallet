<?php
namespace Opencart\Admin\Model\Extension\Ohbono\Module;

/**
 * OHBONO Wallet Event Registry
 *
 * Registers only explicitly verified events. This is intentionally separate
 * from the event definitions so staging can confirm the target OpenCart build
 * before enabling financial checkout hooks.
 */
class WalletEventRegistry extends \Opencart\System\Engine\Model
{
    public function register(array $definitions): array
    {
        if (!is_array($definitions) || !$definitions) {
            return [];
        }

        $registered = [];

        foreach ($definitions as $definition) {
            if (empty($definition['code']) ||
                empty($definition['trigger']) ||
                empty($definition['action'])) {
                continue;
            }

            $code = trim($definition['code']);

            $existing = $this->db->query(
                "SELECT event_id
                 FROM `" . DB_PREFIX . "event`
                 WHERE code = '" .
                    $this->db->escape($code) . "'
                 LIMIT 1"
            );

            if ($existing->num_rows) {
                $registered[] = (int)$existing->row['event_id'];
                continue;
            }

            $this->db->query(
                "INSERT INTO `" . DB_PREFIX . "event`
                 SET code = '" .
                    $this->db->escape($code) . "',
                     description = 'OHBONO Wallet integration event',
                     trigger = '" .
                    $this->db->escape(
                        $definition['trigger']
                    ) . "',
                     action = '" .
                    $this->db->escape(
                        $definition['action']
                    ) . "',
                     status = '" .
                    (int)($definition['status'] ?? 1) . "',
                     sort_order = '" .
                    (int)($definition['sort_order'] ?? 100) . "',
                     date_added = NOW()"
            );

            $registered[] =
                (int)$this->db->getLastId();
        }

        return $registered;
    }

    public function unregister(array $codes): void
    {
        foreach ($codes as $code) {
            $code = trim((string)$code);

            if ($code === '') {
                continue;
            }

            $this->db->query(
                "DELETE FROM `" . DB_PREFIX . "event`
                 WHERE code = '" .
                $this->db->escape($code) . "'"
            );
        }
    }
}
