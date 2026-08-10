<?php
/**
 * OHBONO Wallet Email Cron
 *
 * Usage:
 * php /path/to/opencart/upload/cron/wallet_email_worker.php 20
 */

define('DIR_APPLICATION', dirname(__DIR__) . '/catalog/');
define('DIR_SYSTEM', dirname(__DIR__) . '/system/');
define('DIR_STORAGE', dirname(__DIR__) . '/storage/');

require_once(DIR_SYSTEM . 'framework.php');

$registry = new \Opencart\System\Engine\Registry();

$config = new \Opencart\System\Engine\Config();
$registry->set('config', $config);

$db = new \Opencart\System\Library\DB(
    DB_DRIVER,
    DB_HOSTNAME,
    DB_USERNAME,
    DB_PASSWORD,
    DB_DATABASE,
    DB_PORT
);

$registry->set('db', $db);

$lock_file = DIR_STORAGE . 'cache/ohbono_wallet_email_worker.lock';

if (!is_dir(dirname($lock_file))) {
    mkdir(dirname($lock_file), 0755, true);
}

$handle = fopen($lock_file, 'c+');

if (!$handle || !flock($handle, LOCK_EX | LOCK_NB)) {
    echo json_encode([
        'success' => false,
        'message' => 'Another wallet email worker is already running.'
    ]) . PHP_EOL;
    exit(0);
}

try {
    $worker = new \OhbonoWalletEmailWorker($registry);

    $limit = isset($argv[1])
        ? (int)$argv[1]
        : 20;

    $result = $worker->process($limit);

    echo json_encode([
        'success' => true,
        'result' => $result
    ]) . PHP_EOL;
} finally {
    flock($handle, LOCK_UN);
    fclose($handle);
}
