<?php declare(strict_types=1);

require_once __DIR__ . '/init.php';

$server = tus()->getServer();
$response = $server->serve();
$response->send();

exit(0);
