<?php declare(strict_types=1);

use Mioweb\Library\MwaConnect\MwaConnect;

global $mwaconnect_module;

define('MWACONNECT_VERSION', '0.9');
MW()->add_version('mwaconnect', MWACONNECT_VERSION); // TODO can be removed?

require_once(__DIR__ . '/MwaConnect.php');

$mwaconnect_module = new MwaConnect();
add_action('cms_load_plugin', [$mwaconnect_module, 'connect']);
